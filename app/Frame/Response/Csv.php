<?php

namespace Frame\Response;

class Csv extends Foundation implements ResponseInterface
{

    protected $contentType = 'text/csv';
    protected $downloadFilename = null;

    public function render($params = null)
    {

        $params = ($params ? $params : $this->viewParams);

        if (!is_array($params)) {
            throw new InvalidResponseException('Csv response value must be an array');
        }

        if (!headers_sent()) {
            header('Content-Type: ' . $this->contentType);
            if ($this->downloadFilename != null) {
                header('Content-Disposition: attachment; filename="' . $this->filename . '"');
                header('Content-Transfer-Encoding: binary');
            }
        }

        $fp = fopen('php://output', 'w');
        foreach($params as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);

    }

    /*
     * Set the filename to force the browser to download the CSV
     */
    public function setDownloadFilename($filename)
    {

        $this->downloadFilename = $filename;

    }

}