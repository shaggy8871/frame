<?php

namespace Frame\Response;

class Csv implements ResponseInterface
{

    private $contentType = 'text/csv';
    private $filename = null;

    public function render($values = null)
    {

        if (!is_array($values)) {
            throw new InvalidResponseException('Csv response value must be an array');
        }

        if (!headers_sent()) {
            header('Content-Type: ' . $this->contentType);
            if ($this->filename != null) {
                header('Content-Disposition: attachment; filename="' . $this->filename . '"');
                header('Content-Transfer-Encoding: binary');
            }
        }

        $fp = fopen('php://output', 'w');
        foreach($values as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);

    }

    /*
     * Set the filename to force the browser to download the CSV
     */
    public function setFilename($filename)
    {

        $this->filename = $filename;

    }

    /*
     * Set the content type to something other than the default
     */
    public function setContentType($contentType)
    {

        $this->contentType = $contentType;

    }

}
