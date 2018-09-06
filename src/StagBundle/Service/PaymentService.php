<?php
namespace StagBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use StagBundle\Entity\Participant;
use StagBundle\Entity\Ticket;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentService
{

    private $paymentApiUrl;

    /**
     * Important to set base API url
     * TODO: find better way if exists
     * @param string $paymentApiUrl
     */
    public function _setApiUrl($paymentApiUrl) {
        $this->paymentApiUrl = $paymentApiUrl;
    }

    public function checkTicketParticipantPayment(
        Participant $participant,
        Ticket $ticket,
        EntityManagerInterface $em
    ) {
        if (
            // performance conditions
            empty($participant->getPaymentReferenceNumber())
            || !empty($participant->getDeposit())
            || !empty($participant->getPayment())
        ) {
            return false;
        }

        $participantPayment = json_decode($this->_getPaymentByReference($participant->getPaymentReferenceNumber()));
        if (empty($participantPayment)) {
            return false;
        }

        // TODO: implement multiple payments with the same reference, e.g. deposit and payment payed by wire
        if ($participantPayment->objem < $ticket->getPrice()) {
            $participant->setDeposit('wire');
        } else {
            $participant->setPayment('wire');
        }

        $em->persist($participant);
        $em->flush();
        return true;
    }

    private function _getPaymentByReference($reference) {
        $url = $this->paymentApiUrl . "/get-by-reference";
        $result = $this->_callApi(sprintf(
            '%s/%s',
            $url,
            $reference)
        );
        return $result;
    }

    private function _callApi($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt ($curl, CURLOPT_CAINFO, $this->container->getParameter('cacert_path'));
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}