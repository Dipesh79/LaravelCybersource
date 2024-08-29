<?php

namespace Dipesh79\LaravelCybersource;

use Dipesh79\LaravelCybersource\Exception\InvalidKeyException;

class LaravelCybersource
{

    /**
     * @throws InvalidKeyException
     */
    public function generateFormData(array $paymentData): array
    {
        $this->checkEnvData();

        $this->checkPaymentDataArray($paymentData);

        $signed_field_names = "access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency,payment_method,bill_to_forename,bill_to_surname,bill_to_email,bill_to_phone,bill_to_address_line1,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code";
        $unsigned_field_names = "card_type,card_number,card_expiry_date";
        $signed_date_time = gmdate("Y-m-d\TH:i:s\Z");
        $locale = "en";
        $transaction_type = "sale";


        $data = [
            'access_key' => config('cybersource.access_key'),
            'profile_id' => config('cybersource.profile_id'),
            'transaction_uuid' => $paymentData['transaction_uuid'],
            'signed_field_names' => $signed_field_names,
            'unsigned_field_names' => $unsigned_field_names,
            'signed_date_time' => $signed_date_time,
            'locale' => $locale,
            'transaction_type' => $transaction_type,
            'reference_number' => $paymentData['reference_number'],
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? 'NPR',
            'payment_method' => "card",
            'bill_to_forename' => $paymentData['bill_to_forename'] ?? 'noreal',
            'bill_to_surname' => $paymentData['bill_to_surname'] ?? 'name',
            'bill_to_email' => $paymentData['bill_to_email'] ?? 'demo@gmail.com',
            'bill_to_phone' => $paymentData['bill_to_phone'] ?? '9800000000',
            'bill_to_address_line1' => $paymentData['bill_to_address_line1'] ?? '1295 Charleston Rd',
            'bill_to_address_city' => $paymentData['bill_to_address_city'] ?? 'Mountain View',
            'bill_to_address_state' => $paymentData['bill_to_address_state'] ?? 'CA',
            'bill_to_address_country' => $paymentData['bill_to_address_country'] ?? 'US',
            'bill_to_address_postal_code' => $paymentData['bill_to_address_postal_code'] ?? '94043',
            'card_type' => '001',
            'card_number' => '',
            'card_expiry_date' => '',
            'auth_trans_ref_no' => ''
        ];
        $signed_string = '';

        $signed_field_array = explode(',', $signed_field_names);
        foreach ($signed_field_array as $key => $value) {
            $key_val = $value . '=' . $data[$value];
            if ($key == 0) {
                $signed_string = $key_val;
            } else {
                $signed_string = $signed_string . ',' . $key_val;
            }

        }

        $hash = hash_hmac('sha256', $signed_string, config('cybersource.secret_key'), true);

        $signature = base64_encode($hash);

        $data['signature'] = $signature;

        return $data;
    }

    /**
     * @throws InvalidKeyException
     */
    private function checkEnvData(): void
    {
        if (empty(config('cybersource.access_key'))) {
            throw new InvalidKeyException('Cybersource Access Key is not set in .env file');
        }
        if (empty(config('cybersource.profile_id'))) {
            throw new InvalidKeyException('Cybersource Profile ID is not set in .env file');
        }
        if (empty(config('cybersource.secret_key'))) {
            throw new InvalidKeyException('Cybersource Secret Key is not set in .env file');
        }
    }

    /**
     * @throws InvalidKeyException
     */
    private function checkPaymentDataArray(array $data): void
    {
        $requiredKeys = [
            'transaction_uuid',
            'reference_number',
            'amount'
        ];
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $data)) {
                throw new InvalidKeyException("Key $key is missing in payment data array");
            }
        }

    }

    public function checkPaymentStatus(array $request): array
    {
        if ($request['decision'] == 'ERROR') {
            return [
                'reference_number' => $request['req_reference_number'],
                'transaction_uuid' => $request['req_transaction_uuid'],
                'amount' => $request['req_amount'],
                'payment_status' => false,
                'message' => $request['message']
            ];
        } else {
            return [
                'reference_number' => $request['req_reference_number'],
                'transaction_uuid' => $request['req_transaction_uuid'],
                'amount' => $request['req_amount'],
                'payment_status' => $request['payer_authentication_reason_code'] == 100,
                'message' => $request['message']
            ];
        }
    }

    public function getCancelledData(array $request): array
    {
        return [
            'reference_number' => $request['req_reference_number'],
            'transaction_uuid' => $request['req_transaction_uuid'],
            'amount' => $request['req_amount'],
            'payment_status' => false,
            'message' => $request['message']
        ];

    }


}
