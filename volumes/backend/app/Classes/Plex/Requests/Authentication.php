<?php namespace App\Classes\Plex\Requests;

use App\Classes\Plex\Abstracts\AbstractClient;

/**
 * Class Authentication
 * @package App\Classes\Plex\Requests
 */
class Authentication extends AbstractClient {

    /**
     * ID received from the authentication request
     * @var null|integer
     */
    protected $authenticationID = null;

    /**
     * Code received from the authentication request
     * @var null|string
     */
    protected $authenticationCode = null;

    /**
     * Authentication constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->setHeadersForPlex();
    }

    /**
     * Build OAuth Link For Google
     * @param string $email
     * @return array
     */
    public function buildGoogleOAuthLink(string $email) : array {
        $this->requestAuthenticationCode();
        $contextItems = [
            'product'       =>  $this->product,
            'platform'      =>  $this->platform,
            'device'        =>  $this->device,
            'version'       =>  env('APP_VERSION'),
            'model'         =>  sprintf('%s %s', $this->product, $this->platform),
        ];

        $alsoAppend = [
            'clientID'      =>  $this->clientIdentifier,
            'forwardUrl'    =>  'https://' . env('APP_URL') . '/account/oauth/plex/callback?email=' . $email,
            'code'          =>  $this->authenticationCode
        ];

        $context = [];
        $append = [];

        foreach ($contextItems as $key => $value) {
            $context[] = sprintf('context[device][%s]=%s', $key, urlencode($value));
        }

        foreach ($alsoAppend as $key => $value) {
            $append[] = sprintf('%s=%s', $key, $value);
        }

        return [
            'url'       =>  sprintf('%sauth#!?%s&%s', $this->appUrl, implode('&', $context), implode('&', $append)),
            'code'      =>  $this->authenticationCode,
            'id'        =>  $this->authenticationID
        ];
    }

    /**
     * Authorize user
     * @param int $id
     * @return null|string
     */
    public function authorizeUser(int $id) : ?string {
        $request = $this->client->get($this->resolvePlexApiUri(sprintf('pins/%d', $id)));
        $response = json_decode($request->getBody()->getContents(), true);
        return $response['authToken'] ?? null;
    }

    /**
     * Request authentication code
     * @return Authentication|static|self|$this
     */
    protected function requestAuthenticationCode() : self {
        $request = $this->client->post($this->resolvePlexApiUri('pins?strong=true'));
        $response = json_decode($request->getBody()->getContents(), true);

        $this->authenticationID = $response['id'];
        $this->authenticationCode = $response['code'];
        return $this;
    }


}
