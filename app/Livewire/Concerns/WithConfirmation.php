<?php

namespace App\Livewire\Concerns;

trait WithConfirmation
{
    public bool $confirmed = false;

    public $confirmationModal;

    public $confirmationId;

    public function ensureConfirmation($confirmationId)
    {
        
         $this->confirmationId = $confirmationId;


        if($this->confirmed){
            return true;
        }

        $this->toggleConfirmation();

       return false;
    }

    public function toggleConfirmation()
    {
        if($this->confirmationModal){
            return $this->closeConfirmation();
        }

        $this->confirmationModal =true;
        $this->dispatch('open-modal', id: 'confirm-purchase');   
    }

    public function closeConfirmation()
    {
        if(!$this->confirmationModal){
            return;
        }

        $this->confirmationModal = false;
        $this->dispatch('close-modal', id: 'confirm-purchase');   

    }

    public function confirmation()
    {
        $this->confirmed = true;
        $this->closeConfirmation();
        $this->dispatch('form-confirmed-'.$this->confirmationId);
    }
}
