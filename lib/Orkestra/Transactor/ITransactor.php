<?php

namespace Orkestra\Transactor;

interface ITransactor
{
    public function transact();
    
    public function createTransaction();
}