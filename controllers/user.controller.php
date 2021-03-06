<?php

require_once MODELS_ROOT . 'service.model.php';
require_once CONTROLLERS_ROOT . 'all.php';
class UserController extends Controller
{

    public function index ($id = 0) {
        $ServiceModel = (new serviceModel ())->getModel();

        $User = $ServiceModel->getId($id);

        $views = [];

        $views['user']['billed']  = [
            'value' => is_null($User->getInvoices()) ? '': $User->getInvoices()->format(),
            'class' => ''
        ];

        $views['user']['payed'] = [
            'value' => is_null($User->payed()) ? '' : $User->payed()->format(),
            'class' => is_null($User->payed()) ? '' : Price::getStatus($User->payed()->status()->get())
        ];

        $views['user']['unpayed'] = [
            'value' => is_null($User->unpayed()) ? '' : $User->unpayed()->format(),
            'class' => is_null($User->unpayed()) ? '' : Price::getStatus($User->unpayed()->status()->get())
        ];

        $views['user']['advance'] = [
            'value' => is_null($User->advance()) ? '' : $User->advance()->format(),
            'class' => is_null($User->advance()) ? '' : Price::getStatus($User->advance()->status()->get())
        ];

        $views['options'] = getHeader($ServiceModel);

        $views['titles'] = [
            'page' => (is_null($ServiceModel->options()->name())
                ? 'Repartition'
                : $ServiceModel->options()->name()->get()) . ' - ' . $User->name()->get(),
            'header1' => is_null($User->name())     ? '': $User->name()->get(),
            'header2' => 'Résumé'
        ];

        $this
            ->set($views)
            ->add('index')
            ->history($User);
    }

    /**
     * get layout historique
     * @param  User $User user needed
     * @return void
     */
    protected function history ($User) {
        $invoices = [
            'line'  => [],
            'total' => new Price (),
        ];

        $lines = [];

        // get all bills
        Main::each($User->invoice(), function ($invoice) use (&$invoices, &$lines)
        {
            $lines[] = [
                'price' => [
                    'value' => $invoice->format(),
                    'class' => Price::getStatus($invoice->status()->get()),
                ],
                'date' => $invoice->date()->format('d/m/Y'),
                'url'  => URL . '/bill/' . $invoice->bill()->id
            ];
            $invoices['total']->set($invoice);
        });
        
        
        // return array ti have lastest bills
        $invoices['line'] = array_reverse($lines);

        $invoices['total'] = $invoices['total']->get('price') == 0 ? '' : $invoices['total']->format();
        $views['invoices'] = $invoices;
        $this
            ->set($views)
            ->add('history');
    }
}
