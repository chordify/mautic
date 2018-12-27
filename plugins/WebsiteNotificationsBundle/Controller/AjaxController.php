<?php

namespace MauticPlugin\WebsiteNotificationsBundle\Controller;

use Mautic\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Mautic\CoreBundle\Controller\AjaxLookupControllerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AjaxController extends CommonAjaxController
{
    use AjaxLookupControllerTrait;

    public function getWebsiteNotificationCountStatsAction(Request $request)
    {
        $model = $this->getModel('website_notifications');

        $data = [];
        if ($id = $request->get('id')) {
            if ($stats = $model->getWebsiteNotificationStatsTotal($id)) {
                $readCount      = $stats['read_count'];
                $sentCount      = $stats['sent_count'];
                $readPercentage = $sentCount == 0 ? 0 : round($readCount / $sentCount * 100, 1);
                $data           = [
                    'success'     => 1,
                    'sentCount'   => $this->translator->trans('mautic.email.stat.sentcount', ['%count%' => $sentCount]),
                    'readCount'   => $this->translator->trans('mautic.email.stat.readcount', ['%count%' => $readCount]),
                    'readPercent' => $this->translator->trans('mautic.email.stat.readpercent', ['%count%' => $readPercentage]),
                ];
            }
        }

        return new JsonResponse($data);
    }
}
