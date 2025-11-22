<?php

namespace aiprovider_gemini;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use GuzzleHttp\Psr7\Uri;

/**
 * @property-read \core_ai\aiactions\edit_image $action
 */
class process_edit_image extends process_generate_image {

    #[\Override]
    protected function create_request_object(string $userid): RequestInterface {
        /** @var \stored_file $stored_file */
        $stored_file = $this->action->get_storedfile();

        $requestobj = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $this->action->get_configuration('prompttext')
                        ],
                        [
                            'inline_data' => [
                                'mime_type' => $stored_file->get_mimetype(),
                                'data' => base64_encode($stored_file->get_content()),
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return new Request(
            method: 'POST',
            uri: '',
            body: json_encode($requestobj),
            headers: [
                'Content-Type' => 'application/json',
            ],
        );
    }

    #[\Override]
    protected function handle_api_success(ResponseInterface $response): array {
        /** @var \stored_file $stored_file */
        $stored_file = $this->action->get_storedfile();
        $responsebody = $response->getBody();
        $bodyobj = json_decode($responsebody);

        $candidates = $bodyobj->candidates;

        // I have only one image.
        foreach ($candidates[0]->content->parts as $part) {
            if (isset($part->inlineData->data)) {
                $generatedimage = $part->inlineData->data;

                // Return in the expected format
                return [
                    'success' => true,
                    'imagebase64' => $generatedimage,
                    'sourceurl' => (string) \moodle_url::make_draftfile_url(
                        $stored_file->get_itemid(),
                        $stored_file->get_filepath(),
                        $stored_file->get_filename()
                    )
                ];
            }
        }

        return [
            'success' => false,
        ];
    }

    /**
     * Get the endpoint URI.
     *
     * @return UriInterface
     */
    #[\Override]
    protected function get_endpoint(): UriInterface {
        $requrl = "https://generativelanguage.googleapis.com/v1beta/models/{$this->get_model()}:generateContent";
        return new Uri($requrl);
    }

    /**
     * Get the name of the model to use.
     *
     * @return string
     */
    #[\Override]
    protected function get_model(): string {
        return 'gemini-2.5-flash-image';
    }

}