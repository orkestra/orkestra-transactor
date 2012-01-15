<?php

namespace Orkestra\Transactor\Entities;

interface ITransactor
{
    public function transact();
    
    public function createTransaction();
}