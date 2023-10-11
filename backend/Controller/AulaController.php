<?php

namespace App\Controller;

use App\Database\Crud;

class AulaController extends Crud{
    protected $table;
    public function __construct($classe) {
        parent::__construct();
        $this->table = $classe;
    }
    public function inserir() {
        return $this->insert($this->table);
    }
    public function buscarTodos() {
        return $this->select($this->table,[]);
     }
     public function buscarId($id) {
        return $this->select($this->table,['id' => $id]);
     }
     public function atualizarId($id) {
        return $this->update($this->table ,['id' => $id]);
     }  
    public function excluir($id) {
        return $this->delete($this->table ,['id'=>$id]);
    }
}