<?php
namespace CustomVocab\Controller;

use CustomVocab\Form\CustomVocabForm;
use Omeka\Form\ConfirmForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function browseAction()
    {
        $response = $this->api()->search('custom_vocabs');
        $view = new ViewModel;
        $view->setVariable('vocabs', $response->getContent());
        return $view;
    }

    public function showDetailsAction()
    {
        $response = $this->api()->read('custom_vocabs', $this->params('id'));
        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('resource', $response->getContent());
        return $view;
    }

    public function addAction()
    {
        $form = $this->getForm(CustomVocabForm::class);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());
            if ($form->isValid()) {
                $formData = $form->getData();
                $formData['o:item_set'] = ['o:id' => $formData['o:item_set']];
                $response = $this->api($form)->create('custom_vocabs', $formData);
                if ($response) {
                    $this->messenger()->addSuccess('Custom vocab created.'); // @translate
                    return $this->redirect()->toRoute('admin/custom-vocab');
                }
            } else {
                $this->messenger()->addError('There was an error during validation'); // @translate
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        return $view;
    }

    public function editAction()
    {
        $form = $this->getForm(CustomVocabForm::class);
        $response = $this->api()->read('custom_vocabs', $this->params('id'));
        $vocab = $response->getContent();

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());
            if ($form->isValid()) {
                $formData = $form->getData();
                $formData['o:item_set'] = ['o:id' => $formData['o:item_set']];
                $response = $this->api($form)->update('custom_vocabs', $vocab->id(), $formData);
                if ($response) {
                    $this->messenger()->addSuccess('Custom vocab updated.'); // @translate
                    return $this->redirect()->toRoute('admin/custom-vocab');
                }
            } else {
                $this->messenger()->addError('There was an error during validation'); // @translate
            }
        } else {
            $data = $vocab->jsonSerialize();
            $data['o:item_set'] = $data['o:item_set'] ? $data['o:item_set']->id() : null;
            $form->setData($data);
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('vocab', $vocab);
        return $view;
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isPost()) {
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $response = $this->api($form)->delete('custom_vocabs', $this->params('id'));
                if ($response) {
                    $this->messenger()->addSuccess('Vocab successfully deleted'); // @translate
                }
            } else {
                $this->messenger()->addError('Vocab could not be deleted'); // @translate
            }
        }
        return $this->redirect()->toRoute('admin/custom-vocab');
    }

    public function deleteConfirmAction()
    {
        $resource = $this->api()->read('custom_vocabs', $this->params('id'))->getContent();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('common/delete-confirm-details');
        $view->setVariable('resource', $resource);
        $resourceLabel = 'custom vocab'; // @translate
        $view->setVariable('resourceLabel', $resourceLabel);
        $view->setVariable('partialPath', 'custom-vocab/index/show-details');
        return $view;
    }
}
