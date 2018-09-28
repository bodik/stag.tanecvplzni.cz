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
    private $cacertPath;

    /**
     * Important to set parameters
     * TODO: find better way if exists
     * @param string $paymentApiUrl
     */
    public function _setParameters($paymentApiUrl, $cacertPath) {
        $this->paymentApiUrl = $paymentApiUrl;
        $this->cacertPath = $cacertPath;
    }

    /**
     * @param Participant[] $participants
     * @param EntityManagerInterface $em
     */
    public function checkTicketParticipantsPayments(
        array $participants,
        EntityManagerInterface $em
    ) {
        $participantsRefNumbers = array();
        foreach ($participants as $participant) {
            if (!empty($participant->getPaymentReferenceNumber())) {

                if ( // performance conditions
                    empty($participant->getPaymentReferenceNumber())
                    || !empty($participant->getPayment())
                ) {
                    continue;
                }

                array_push($participantsRefNumbers, $participant->getPaymentReferenceNumber());
            }
        }

        $participantsPayments = json_decode($this->_getPaymentsByReferences(join(',', $participantsRefNumbers)),true);

        foreach ($participants as $participant) {
            if (!empty($participantsPayments[$participant->getPaymentReferenceNumber()])) {

                // TODO: implement multiple payments with the same reference, e.g. deposit and payment payed by wire
                if ($participantsPayments[$participant->getPaymentReferenceNumber()] < $participant->getTicketRef()->getPrice()) {
                    $participant->setDeposit('wire');
                } else {
                    $participant->setPayment('wire');
                }

                $em->persist($participant);
                $em->flush();
            }
        }
    }

    private function _getPaymentsByReferences($reference) {
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
        curl_setopt ($curl, CURLOPT_CAINFO, $this->cacertPath);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}