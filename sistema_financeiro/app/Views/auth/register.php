<h1 class="auth-form-title">Cadastre-se</h1>
<form id="registerForm" data-auth-action="register">
    <div class="auth-field"><label for="registerName">Nome</label>
        <div class="auth-input-group"><i class="bi bi-person"></i><input type="text" id="registerName" name="name" placeholder="Insira seu nome" minlength="3" required></div>
    </div>
    <div class="auth-field"><label for="registerEmail">E-mail</label>
        <div class="auth-input-group"><i class="bi bi-envelope"></i><input type="email" id="registerEmail" name="email" placeholder="exemplo@email.com" required></div>
    </div>
    <div class="auth-field"><label for="registerPassword">Senha</label>
        <div class="auth-input-group"><i class="bi bi-lock"></i><input type="password" id="registerPassword" name="password" placeholder="Insira sua senha" minlength="8" required></div>
    </div>
    <div class="auth-field"><label for="registerConfirmPassword">Confirmar senha</label>
        <div class="auth-input-group"><i class="bi bi-lock-fill"></i><input type="password" id="registerConfirmPassword" name="password_confirmation" placeholder="Confirme sua senha" minlength="8" required></div>
    </div>
    <p id="authError" class="auth-error d-none" role="alert"></p>
    <div class="auth-actions"><button type="submit" class="auth-primary-btn">Criar conta</button><a href="<?= e(url('/login')) ?>" class="auth-secondary-btn">Realizar login</a></div>
</form>