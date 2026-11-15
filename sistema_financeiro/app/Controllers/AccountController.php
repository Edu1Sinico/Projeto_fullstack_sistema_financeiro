<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Request; use App\Core\Response; use App\Core\Validator; use App\Models\Account;
/** Gerencia as contas financeiras. O saldo só é definido no cadastro e depois muda por transações. */
final class AccountController extends Controller
{
    /** Lista somente as contas do usuário identificado pelo JWT. */
    public function index(Request $r): never { Response::json(Account::allForUser($this->userId($r))); }
    public function store(Request $r): never { $d=$this->validated($r,true); Response::json(Account::create($this->userId($r),$d),201); }
    public function show(Request $r): never { $a=Account::findForUser($this->id($r,'account'),$this->userId($r))??$this->notFound(); Response::json($a); }
    /** O saldo não é editado diretamente; ele muda por transações. */
    public function update(Request $r): never { $id=$this->id($r,'account'); Account::findForUser($id,$this->userId($r))??$this->notFound(); Response::json(Account::update($id,$this->userId($r),$this->validated($r,false))); }
    public function destroy(Request $r): never { $id=$this->id($r,'account'); Account::findForUser($id,$this->userId($r))??$this->notFound(); Account::delete($id,$this->userId($r)); Response::json(null,204); }
    private function validated(Request $r,bool $creating): array { $rules=['name'=>['required','string','min:3','max:100'],'type'=>['required','in:'.implode(',',Account::TYPES)],'status'=>[$creating?'sometimes':'required','in:'.implode(',',Account::STATUSES)]];if($creating){$rules['balance']=['sometimes','numeric','min:0'];}$d=Validator::validate($r->all(),$rules);$d['status']=$d['status']??'active';$d['balance']=$d['balance']??0;return $d; }
}
