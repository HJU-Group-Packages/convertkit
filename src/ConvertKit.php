<?php

namespace HJUGroup\ConvertKit;

use GuzzleHttp\Client;

class ConvertKit
{
    /**
     * @var string
     */
    private $api_secret;
    /**
     * @var string
     */
    private $form_id;
    /**
     * @var string
     */
    private $api_key;

    public function __construct()
    {
        if (config('convertkit.enabled')) {
            $this->api_secret = config('convertkit.api_secret');
            $this->api_key = config('convertkit.api_key');
            $this->form_id = config('convertkit.form_id');

            if (! $this->api_secret) {
                throw new \Exception('ConvertKit API secret not found');
            }

            $this->client = new Client([
                'base_uri' => config('convertkit.base_url'),
            ]);
        }
    }

    /**
     * @param \Illuminate\Support\Collection $collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function sendCollectionToForm($collection)
    {
        if (! config('convertkit.enabled')) {
            return;
        }

        if (! isset($collection['email'])) {
            throw new \Exception('Email was not passed in');
        }

        $collection->put('api_secret', $this->api_secret);

        $result = $this->client->post('forms/' . $this->form_id  . '/subscribe',[
            'form_params' => $collection->toArray(),
        ]);

        $json = json_decode($result->getBody(), true);

        if (empty($json)) {
            throw new \Exception('JSON response not valid or no forms found');
        }
    }

    /**
     * @param string $email
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function unsubscribeSubscriber(string $email)
    {
        if (! config('convertkit.enabled')) {
            return;
        }

        $result = $this->client->put('unsubscribe', [
            'form_params' => [
                'api_secret' => $this->api_secret,
                'email'      => $email,
            ],
        ]);

        $json = json_decode($result->getBody(), true);

        if (empty($json)) {
            throw new \Exception('JSON response not valid or no forms found');
        }

        if ($json['email'] != $email) {
            throw new \Exception('Email address not found');
        }
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function getCustomFields(): array
    {
        if (! config('convertkit.enabled')) {
            return [];
        }

        $result = $this->client->get('custom_fields',[
            'form_params' => [
                'api_key' => $this->api_key,
            ],
        ]);

        $fields = json_decode($result->getBody(), true);

        if (empty($fields)) {
            throw new \Exception('JSON response not valid or no custom fields found');
        }

        $customFields = [];

        foreach ($fields['custom_fields'] as $field) {
            $customFields[] = $field['key'];
        }

        return $customFields;
    }

    /**
     * @param \Illuminate\Support\Collection $collection
     * @param bool $errors
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function isCustomFieldsValid($collection, $errors = true): bool
    {
        $remoteFields = collect($this->getCustomFields());

        $fields = ($collection->has('fields')) ? $collection['fields'] : $collection->toArray();

        foreach ($fields as $key => $field) {
            if (! $remoteFields->contains($key)) {
                if ($errors) {
                    throw new \Exception('Field (' . $key . ') found not in ConvertKit');
                }

                return false;
            }
        }

        return true;
    }

    /**
     * @param \Illuminate\Support\Collection $collection
     * @param bool $errors
     * @return bool
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function isCollectionValid($collection, $errors = true): bool
    {
        if (! config('convertkit.enabled')) {
            return true;
        }

        // Only required field
        if (! isset($collection['email'])) {
            if ($errors) {
                throw new \Exception('Email field missing');
            }
            return false;
        }

        if ($collection->has('fields')) {
            if (! $this->isCustomFieldsValid(collect($collection['fields']), $errors)) {
                return false;
            }
        }

        $collection = $collection->forget('email')
                                 ->forget('fields')
                                 ->forget('first_name');

        if ($collection->count() != 0) {
            if ($errors) {
                throw new \Exception('Email and first name are the only valid root fields');
            }

            return false;
        }

        return true;
    }
}
