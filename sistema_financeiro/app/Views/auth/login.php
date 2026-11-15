<h1 class="auth-form-title">Acesso ao Sistema</h1>
<form id="loginForm" data-auth-action="login">
    <div class="auth-field"><label for="loginEmail">E-mail</label><div class="auth-input-group"><i class="bi bi-envelope"></i><input type="email" id="loginEmail" name="email" placeholder="exemplo@email.com" autocomplete="email" required></div></div>
    <div class="auth-field"><label for="loginPassword">Senha</label><div class="auth-input-group"><i class="bi bi-lock"></i><input type="password" id="loginPassword" name="password" placeholder="Insira sua senha" autocomplete="current-password" required></div></div>
    <div class="auth-options"><label class="auth-remember"><input type="checkbox" name="remember"><span>Manter conectado</span></label><span class="auth-forgot-password text-muted" title="Funcionalidade não implementada">Recuperar senha</span></div>
    <p id="authError" class="auth-error d-none" role="alert"></p><div class="auth-actions"><button type="submit" class="auth-primary-btn">Entrar</button><a href="<?= e(url('/cadastro')) ?>" class="auth-secondary-btn">Cadastre-se</a></div>
</form>
