<?php
namespace Filcronet\SyliusSetefiPlugin\Exception;
use Payum\Core\Reply\HttpResponse;
class HttpGetRedirect extends HttpResponse
{
    /**
     * @var string
     */
    protected $url;
    /**
     * @var array
     */
    protected $fields;
    /**
     * @param string   $url
     * @param array    $fields
     * @param int      $statusCode
     */
    public function __construct($url, array $fields = array(), $statusCode = 200)
    {
        $this->url = $url;
        $this->fields = $fields;
        parent::__construct($this->prepareContent($url, $fields), $statusCode);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string $url
     * @param array  $fields
     *
     * @return string
     */
    protected function prepareContent($url, array $fields)
    {

        $content = <<<'HTML'
        <!DOCTYPE html>
        <html>
            <head>
                <title>Redirecting...</title>
            </head>
            <body onload="document.getElementById('link').click();">
                <p>Redirecting to payment page...</p>
                <a id="link" href="%1$s" class="button">Go to Page</a>
            </body>
        </html>
        HTML;
        return sprintf($content, htmlspecialchars($url, ENT_QUOTES, 'UTF-8'));
    }
}


