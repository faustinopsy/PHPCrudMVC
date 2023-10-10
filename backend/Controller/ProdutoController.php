<?php

namespace App\Controller;

use App\Database\Crud;

class ProdutoController extends Crud{
    protected $user;
    protected $table;
    public function __construct($classe) {
        parent::__construct();
        $this->table = $classe;
    }
    public function buscarTodos(){
        $user = $this->select($this->table);
        
        return  $user;
    }
    public function inserir(){
        if($this->insert($this->table)){
            return true;
        }
        return false;
    }
    public function atualizarId($id){
        if($this->update($this->table ,['id' => $id])){
            return true;
        }
        return false;
    }
    public function excluir( $id){
        if($this->delete($this->table ,['id'=>$id])){
            return true;
        }
        return false;
        
    }
}
