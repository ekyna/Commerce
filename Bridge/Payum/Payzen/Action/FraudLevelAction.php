<?php

namespace Ekyna\Component\Commerce\Bridge\Payum\Payzen\Action;

use Ekyna\Component\Commerce\Bridge\Payum\Request\FraudLevel;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

/**
 * Class FraudAction
 * @package Ekyna\Component\Commerce\Bridge\Payum\Payzen\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FraudLevelAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param FraudLevel $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        if ($payment->getState() !== PaymentStates::STATE_FAILED) {
            return;
        }

        $model = ArrayObject::ensureArrayObject($payment->getDetails());

        if (false == $result = $model['vads_auth_result']) {
            return;
        }

        switch ($result) {
            case '33': // Date de validité de la carte dépassée
            case '54': // Date de validité de la carte dépassée
                $request->setLevel(1);

                return;

            case '03': // Accepteur invalide
            case '04': // Conserver la carte
            case '05': // Ne pas honorer
            case '07': // Conserver la carte, conditions spéciales
            case '12': // Transaction invalide
            case '13': // Montant invalide
                $request->setLevel(3);

                return;

            case '34': // Suspicion de fraude
            case '59': // Suspicion de fraude
                $request->setLevel(4);

                return;

            case '14': // Numéro de porteur invalide
            case '15': // Emetteur de carte inconnu
            case '31': // Identifiant de l’organisme acquéreur inconnu
            case '41': // Carte perdue
            case '43': // Carte volée
            case '56': // Carte absente du fichier
            case '57': // Transaction non permise à ce porteur
            case '63': // Règles de sécurité non respectées
            case '76': // Porteur déjà en opposition, ancien enregistrement conservé
                $request->setLevel(5);

                return;
        }

        if ($model['vads_ctx_mode'] === 'TEST' && $result === '51') { // Provision insuffisante ou crédit dépassé
            $request->setLevel(3);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof FraudLevel
            && $request->getModel() instanceof PaymentInterface;
    }
}