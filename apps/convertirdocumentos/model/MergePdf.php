<?php

namespace convertirdocumentos\model;

use Mpdf\Mpdf;

class MergePdf extends Mpdf
{

    public function mergePdfFiles(array $filenames, $outFile)
    {
        if (!empty($filenames)) {
            $filesTotal = count($filenames);
            $fileNumber = 1;

            if (!file_exists($outFile)) {
                $handle = fopen($outFile, 'wb');
                fclose($handle);
            }

            foreach ($filenames as $fileName) {
                if (file_exists($fileName)) {
                    $pagesInFile = $this->setSourceFile($fileName);
                    for ($i = 1; $i <= $pagesInFile; $i++) {
                        $tplId = $this->importPage($i); // in mPdf v8 should be 'importPage($i)'
                        $this->useTemplate($tplId);
                        if (($fileNumber < $filesTotal) || ($i != $pagesInFile)) {
                            $this->WriteHTML('<pagebreak />');
                        }
                    }
                }
                $fileNumber++;
            }
            return $this->Output($outFile,'S');
        }
        return null;
    }
}