<?php
/* ==========================================================================
   Formulário de usuário — usado no cadastro E na edição
   --------------------------------------------------------------------------
   Como os dois formulários são iguais, o HTML fica em um arquivo só e é
   incluído por usuarios-cadastrar.php e usuarios-editar.php.

   A página que inclui este arquivo deve definir:
     $modo             → 'cadastrar' ou 'editar'
     $acao             → endereço que recebe o formulário (action)
     $dados            → valores dos campos (do banco ou do envio anterior)
     $erros            → erros da validação do PHP [campo => mensagem]
     $voltarPara       → página do botão "Cancelar"
     $mostrarPermissoes→ true para exibir os campos Perfil e Status
     $registro         → (só na edição) linha original do banco
     $podeExcluir      → (só na edição) true para exibir o botão de excluir
   ========================================================================== */

$fotoPadrao = '../img/avatar-padrao.svg';
$fotoAtual  = !empty($dados['foto']) ? '../' . $dados['foto'] : $fotoPadrao;
?>
        <?php if ($erros): ?>
        <div class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-octagon"></i>
          <div><strong>Não foi possível salvar.</strong><br>Verifique os campos destacados abaixo.</div>
          <button type="button" class="alert__close" aria-label="Fechar"><i class="bi bi-x-lg"></i></button>
        </div>
        <?php endif; ?>

        <!-- enctype="multipart/form-data" é obrigatório por causa do upload da foto -->
        <form id="formUsuario" data-modo="<?= $modo ?>" action="<?= e($acao) ?>" method="post" enctype="multipart/form-data" novalidate>
          <?= csrfCampo() ?>
          <input type="hidden" name="remover_foto" id="removerFoto" value="0">

          <div class="form-layout">

            <!-- ================= COLUNA PRINCIPAL ================= -->
            <section class="card">
              <div class="card__body">

                <!-- Seção 1: dados pessoais -->
                <div class="form-section">
                  <h2 class="form-section__title">Dados pessoais</h2>
                  <p class="form-section__desc">Informações de identificação do usuário.</p>

                  <div class="form-grid">
                    <div class="form-group col-12<?= erroClasse($erros, 'foto') ?>">
                      <span class="form-label">Foto de perfil</span>
                      <div class="avatar-upload">
                        <img src="<?= e($fotoAtual) ?>" alt="Pré-visualização da foto" class="avatar-upload__preview" id="previewFoto" data-padrao="<?= $fotoPadrao ?>">
                        <div class="avatar-upload__actions">
                          <div class="btns">
                            <button type="button" class="btn btn-light btn-sm" id="btnEscolherFoto"><i class="bi bi-upload"></i> Enviar foto</button>
                            <button type="button" class="btn btn-ghost btn-sm<?= empty($dados['foto']) ? ' hidden' : '' ?>" id="btnRemoverFoto"><i class="bi bi-x-lg"></i> Remover</button>
                          </div>
                          <span class="form-hint mt-0">JPG, PNG, WEBP ou GIF. Tamanho máximo de 2 MB.</span>
                        </div>
                        <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp,image/gif">
                      </div>
                      <div class="invalid-feedback"><?= erroTexto($erros, 'foto') ?></div>
                    </div>

                    <div class="form-group col-12<?= erroClasse($erros, 'nome') ?>">
                      <label class="form-label" for="nome">Nome completo <span class="req">*</span></label>
                      <input type="text" class="form-control" id="nome" name="nome" placeholder="Ex.: Maria da Silva" maxlength="100" value="<?= e($dados['nome']) ?>">
                      <div class="invalid-feedback"><?= erroTexto($erros, 'nome') ?></div>
                    </div>

                    <div class="form-group col-6<?= erroClasse($erros, 'email') ?>">
                      <label class="form-label" for="email">E-mail <span class="req">*</span></label>
                      <div class="input-icon">
                        <i class="bi bi-envelope"></i>
                        <input type="email" class="form-control" id="email" name="email" placeholder="nome@exemplo.com" maxlength="150" value="<?= e($dados['email']) ?>">
                      </div>
                      <div class="invalid-feedback"><?= erroTexto($erros, 'email') ?></div>
                    </div>

                    <div class="form-group col-6<?= erroClasse($erros, 'telefone') ?>">
                      <label class="form-label" for="telefone">Telefone</label>
                      <div class="input-icon">
                        <i class="bi bi-telephone"></i>
                        <input type="tel" class="form-control" id="telefone" name="telefone" placeholder="(77) 99999-9999" maxlength="20" value="<?= e($dados['telefone']) ?>">
                      </div>
                      <div class="invalid-feedback"><?= erroTexto($erros, 'telefone') ?></div>
                    </div>
                  </div>
                </div>

                <!-- Seção 2: acesso -->
                <div class="form-section" id="secao-acesso">
                  <h2 class="form-section__title">Dados de acesso</h2>
                  <p class="form-section__desc">Credenciais usadas para entrar no painel. O e-mail e o usuário devem ser únicos.</p>

                  <div class="form-grid">
                    <div class="form-group col-12<?= erroClasse($erros, 'usuario') ?>">
                      <label class="form-label" for="usuario">Nome de usuário <span class="req">*</span></label>
                      <div class="input-icon">
                        <i class="bi bi-at"></i>
                        <input type="text" class="form-control" id="usuario" name="usuario" placeholder="maria.silva" maxlength="30" autocomplete="off" value="<?= e($dados['usuario']) ?>">
                      </div>
                      <div class="invalid-feedback"><?= erroTexto($erros, 'usuario') ?></div>
                    </div>

                    <!-- A senha NUNCA é devolvida ao formulário: os campos sempre voltam vazios -->
                    <div class="form-group col-6<?= erroClasse($erros, 'senha') ?>">
                      <?php if ($modo === 'cadastrar'): ?>
                        <label class="form-label" for="senha">Senha <span class="req">*</span></label>
                      <?php else: ?>
                        <label class="form-label" for="senha">Nova senha</label>
                      <?php endif; ?>
                      <div class="input-icon has-toggle">
                        <i class="bi bi-lock"></i>
                        <input type="password" class="form-control" id="senha" name="senha" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                        <button type="button" class="toggle-password" aria-label="Mostrar senha"><i class="bi bi-eye"></i></button>
                      </div>
                      <div class="invalid-feedback"><?= erroTexto($erros, 'senha') ?></div>
                      <div class="password-meter" id="medidorSenha" data-level="0"><span></span><span></span><span></span><span></span></div>
                      <div class="password-meter__text" id="textoForca">Use 8+ caracteres com letras maiúsculas, números e símbolos.</div>
                    </div>

                    <div class="form-group col-6<?= erroClasse($erros, 'confirmar_senha') ?>">
                      <label class="form-label" for="confirmarSenha">Confirmar senha<?= $modo === 'cadastrar' ? ' <span class="req">*</span>' : '' ?></label>
                      <div class="input-icon has-toggle">
                        <i class="bi bi-lock"></i>
                        <input type="password" class="form-control" id="confirmarSenha" name="confirmar_senha" placeholder="Repita a senha" autocomplete="new-password">
                        <button type="button" class="toggle-password" aria-label="Mostrar senha"><i class="bi bi-eye"></i></button>
                      </div>
                      <div class="invalid-feedback"><?= erroTexto($erros, 'confirmar_senha') ?></div>
                      <?php if ($modo === 'editar'): ?>
                        <div class="form-hint">Deixe em branco para manter a senha atual.</div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>

                <!-- Seção 3: conteúdo em HTML (editor rich text) -->
                <div class="form-section">
                  <h2 class="form-section__title">Apresentação</h2>
                  <p class="form-section__desc">Texto exibido no perfil do autor no site. O conteúdo é salvo em HTML.</p>

                  <div class="form-group<?= erroClasse($erros, 'biografia') ?>">
                    <label class="form-label" for="biografia">Biografia <span class="req">*</span></label>
                    <!-- O Summernote transforma este textarea em um editor visual.
                         Dentro do <textarea> o HTML é escapado com e(): o navegador
                         desfaz o escape e entrega o HTML original ao editor. -->
                    <textarea id="biografia" name="biografia" required><?= e($dados['biografia']) ?></textarea>
                    <div class="invalid-feedback"><?= erroTexto($erros, 'biografia') ?></div>
                  </div>
                </div>

              </div>

              <div class="card__footer">
                <span class="form-hint mt-0"><span class="req" style="color: var(--danger)">*</span> Campos obrigatórios</span>
                <div class="d-flex gap-1">
                  <a href="<?= e($voltarPara) ?>" class="btn btn-light">Cancelar</a>
                  <button type="submit" class="btn btn-primary" id="btnSalvar"><i class="bi bi-check-lg"></i> <?= $modo === 'cadastrar' ? 'Cadastrar usuário' : 'Salvar alterações' ?></button>
                </div>
              </div>
            </section>

            <!-- ================= COLUNA LATERAL ================= -->
            <div class="form-layout__aside">

              <?php if ($mostrarPermissoes): ?>
              <section class="card">
                <div class="card__header"><h2 class="card__title">Permissões</h2></div>
                <div class="card__body">
                  <div class="form-group mb-2<?= erroClasse($erros, 'perfil') ?>">
                    <span class="form-label">Perfil de acesso <span class="req">*</span></span>
                    <div class="radio-cards">
                      <label class="radio-card">
                        <input type="radio" name="perfil" value="admin" <?= $dados['perfil'] === 'admin' ? 'checked' : '' ?>>
                        <span class="radio-card__box">
                          <i class="bi bi-shield-lock"></i>
                          <span><strong>Administrador</strong><span>Acesso total, inclusive aos usuários.</span></span>
                        </span>
                      </label>
                      <label class="radio-card">
                        <input type="radio" name="perfil" value="editor" <?= $dados['perfil'] === 'editor' ? 'checked' : '' ?>>
                        <span class="radio-card__box">
                          <i class="bi bi-pencil-square"></i>
                          <span><strong>Editor</strong><span>Gerencia apenas o conteúdo do site.</span></span>
                        </span>
                      </label>
                    </div>
                    <div class="invalid-feedback"><?= isset($erros['perfil']) ? erroTexto($erros, 'perfil') : 'Selecione um perfil de acesso.' ?></div>
                  </div>

                  <div class="form-group<?= erroClasse($erros, 'status') ?>">
                    <span class="form-label">Status</span>
                    <label class="switch">
                      <input type="checkbox" id="status" name="status" value="1" <?= $dados['status'] == 1 ? 'checked' : '' ?>>
                      <span class="switch__track"></span>
                      <span class="switch__label" id="statusTexto">Ativo</span>
                    </label>
                    <div class="invalid-feedback"><?= erroTexto($erros, 'status') ?></div>
                  </div>
                </div>
              </section>
              <?php endif; ?>

              <?php if ($modo === 'cadastrar'): ?>
              <section class="card">
                <div class="card__body">
                  <h2 class="card__title mb-0"><i class="bi bi-lightbulb" style="color: var(--warning)"></i> Dica</h2>
                  <p class="card__subtitle mb-0">O nome de usuário é sugerido automaticamente a partir do nome completo, mas você pode alterá-lo.</p>
                </div>
              </section>
              <?php else: ?>
              <!-- Informações do registro (somente na edição) -->
              <section class="card">
                <div class="card__header"><h2 class="card__title">Informações do registro</h2></div>
                <div class="card__body">
                  <ul class="meta-list">
                    <li><span>ID</span><strong>#<?= $registro['id'] ?></strong></li>
                    <li><span>Perfil</span><strong><?= nomePerfil($registro['perfil']) ?></strong></li>
                    <li><span>Cadastrado em</span><strong><?= formatarData($registro['criado_em']) ?></strong></li>
                    <li><span>Última atualização</span><strong><?= formatarData($registro['atualizado_em'], true) ?></strong></li>
                    <li><span>Último acesso</span><strong><?= formatarData($registro['ultimo_acesso'], true) ?></strong></li>
                  </ul>
                </div>
              </section>
              <?php endif; ?>

              <?php if (!empty($podeExcluir)): ?>
              <section class="card">
                <div class="card__body">
                  <h2 class="card__title mb-0">Excluir usuário</h2>
                  <p class="card__subtitle mb-1">O usuário perderá o acesso ao painel imediatamente.</p>
                  <button type="button" class="btn btn-light btn-sm btn-block" data-modal-open="#modalExcluir" style="color: var(--danger-text)">
                    <i class="bi bi-trash3"></i> Excluir este usuário
                  </button>
                </div>
              </section>
              <?php endif; ?>

            </div>

          </div>
        </form>
