<?php
namespace App\Controller;

use App\Database\Crud;

class UserController extends Crud{
    protected $user;
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
    public function buscarEmail($email) {
       return $this->select($this->table,['email' => $email]);
    }
    public function buscarid($id) {
        return $this->select($this->table,['id' => $id]);
     }
    public function atualizarEmail($email) {
        return $this->update($this->table ,['email' => $email]);
    }   
    public function atualizarId($id) {
        return $this->update($this->table ,['id' => $id]);
    }   
     public function excluir($id) {
        return $this->delete($this->table ,['id'=>$id]);
    }
}
