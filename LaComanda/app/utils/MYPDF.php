<?php

class MYPDF extends TCPDF 
{
    public function Header()
    {
        $bMargin = $this->getBreakMargin();
        $auto_page_break = $this->AutoPageBreak;
        $this->SetAutoPageBreak(false, 0);
        $img_file = 'logo/logoComanda.jpg';
        $this->Image($img_file, 7, 7, 35, 35, '', '', '', false, 300, '', false, false, 0);
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        $this->setPageMark();
    }
    
    public function Footer() 
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 8);
        $this->html = '<p style="border-top:1px solid #999; text-align:center;">
                        Leandro Grimbe
                        </p>';
        $this->writeHTML($this->html, true, false, true, false, '');
    }
}
