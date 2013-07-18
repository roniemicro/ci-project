<?php
/**
 * Wrapper Class For Swift_Mailer Library
 * @Author: Roni Kumar Saha
 *        Date: 7/12/12
 *        Time: 3:48 PM
 */
class EasyMail
{

    private $_mailer=null;
    private $_message=null;

    function __construct(){
       require_once (realpath(APPPATH.'../vendor/Swift/swift_required.php'));
       $this->_mailer=$this->_mailer_init();
    }

    public function Mailer(){
        return $this->_mailer;
    }

    public function Message($subject=""){
        if($this->_message===null){
            $this->_message=$this->newInstance($subject);
        }
        return $this->_message;
    }

    private function newInstance($subject=""){
        return Swift_Message::newInstance($subject);
    }

    public function mail($to,$subject,$body,$from){
        if($this->_message===null){
            $this->_message=$this->newInstance();
        }

        $this->_message->setFrom($from)
            ->setBody($body,'text/html','UTF-8')
            ->setTo($to)
            ->setSubject($subject);
        $this->send();
    }

    public function setBody($body){
        $this->_message->setBody($body,'text/html','UTF-8');
        return $this->_message;
    }

    public function setFrom($from){
        $this->_message->setFrom($from);
        return $this->_message;
    }
    public function setTo($to){
        $this->_message->setTo($to);
        return $this->_message;
    }

    public function setSubject($subject){
        $this->_message->setSubject($subject);
        return $this->_message;
    }

    public function send(){
        if($this->_mailer->send($this->_message)){
            return true;
        }
        return false;
    }

    public function attach($path,$name=null){
        $this->_message->attach(Swift_Attachment::fromPath($path,$name));
    }

    private function _mailer_init(){
        $transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
        // Create the Mailer using your created Transport
        return  Swift_Mailer::newInstance($transport);
    }
}
