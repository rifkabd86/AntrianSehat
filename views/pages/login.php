<div class="auth-page" data-auth-page>
    <section class="auth-visual" aria-label="Pelayanan kesehatan">
        <img class="auth-visual-image" src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1600&q=85" alt="Tenaga medis memberikan pelayanan kepada pasien">
        <div class="auth-visual-shade"></div>
        <a class="brand-lockup brand-lockup-light" href="<?= SecurityHelper::escape(UrlHelper::to()) ?>">
            <span class="brand-symbol"><i class="bi bi-heart-pulse-fill" aria-hidden="true"></i></span>
            <span><strong>ANTRIAN SEHAT</strong><small>LAYANAN KESEHATAN TERPADU</small></span>
        </a>
        <div class="auth-visual-copy">
            <span class="eyebrow eyebrow-light">PUSKESMAS DIGITAL</span>
            <h1>Pelayanan Sehat,<br>Antrean Lebih Cepat.</h1>
            <p>Informasi pendaftaran dan antrean yang lebih jelas, untuk pelayanan yang terasa lebih dekat.</p>
            <div class="auth-visual-note"><span class="note-dot"></span> Pelayanan dimulai dengan pengalaman yang lebih baik</div>
        </div>
        <div class="auth-visual-footer"><span>ANTRIAN SEHAT</span><span>01 / 04</span></div>
    </section>

    <section class="auth-form-side">
        <div class="auth-form-wrap">
            <a class="brand-lockup brand-lockup-mobile" href="<?= SecurityHelper::escape(UrlHelper::to()) ?>">
                <span class="brand-symbol"><i class="bi bi-heart-pulse-fill" aria-hidden="true"></i></span>
                <span><strong>ANTRIAN SEHAT</strong><small>LAYANAN KESEHATAN TERPADU</small></span>
            </a>

            <div class="auth-form-state" data-auth-view="login">
                <span class="eyebrow">SELAMAT DATANG</span>
                <h2>Masuk ke akun Anda</h2>
                <p class="auth-intro">Gunakan email terdaftar untuk melanjutkan ke layanan.</p>
                <form class="auth-form" data-demo-form>
                    <label class="form-label" for="login-email">Email</label>
                    <div class="field-wrap"><i class="bi bi-envelope" aria-hidden="true"></i><input id="login-email" class="form-control" type="email" placeholder="nama@email.com" autocomplete="email" required></div>

                    <div class="field-heading"><label class="form-label" for="login-password">Password</label><a href="#reset" data-toast="Fitur pemulihan password belum diaktifkan.">Lupa password?</a></div>
                    <div class="field-wrap"><i class="bi bi-lock" aria-hidden="true"></i><input id="login-password" class="form-control password-input" type="password" placeholder="Masukkan password" autocomplete="current-password" required><button class="field-action" type="button" data-password-toggle="login-password" aria-label="Tampilkan password"><i class="bi bi-eye" aria-hidden="true"></i></button></div>

                    <div class="form-check auth-remember"><input class="form-check-input" type="checkbox" value="1" id="remember"><label class="form-check-label" for="remember">Ingat saya</label></div>
                    <button class="btn btn-primary auth-submit" type="submit">Masuk <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                </form>
                <p class="auth-switch">Belum memiliki akun? <button type="button" class="text-link" data-auth-switch="register">Daftar sebagai pasien</button></p>
            </div>

            <div class="auth-form-state" data-auth-view="register" hidden>
                <span class="eyebrow">PENDAFTARAN PASIEN</span>
                <h2>Buat akun pasien</h2>
                <p class="auth-intro">Lengkapi data berikut untuk memulai.</p>
                <form class="auth-form register-form" data-demo-form>
                    <div class="register-grid">
                        <div class="register-field register-field-wide"><label class="form-label" for="register-name">Nama lengkap</label><input id="register-name" class="form-control" type="text" placeholder="Nama sesuai identitas" autocomplete="name" required></div>
                        <div class="register-field"><label class="form-label" for="register-nik">NIK</label><input id="register-nik" class="form-control" type="text" inputmode="numeric" minlength="16" maxlength="16" placeholder="16 digit NIK" required></div>
                        <div class="register-field"><label class="form-label" for="register-phone">Nomor telepon</label><input id="register-phone" class="form-control" type="tel" placeholder="08xx xxxx xxxx" autocomplete="tel" required></div>
                        <div class="register-field register-field-wide"><label class="form-label" for="register-email">Email</label><input id="register-email" class="form-control" type="email" placeholder="nama@email.com" autocomplete="email" required></div>
                        <div class="register-field"><label class="form-label" for="register-password">Password</label><div class="field-wrap field-wrap-compact"><input id="register-password" class="form-control" type="password" minlength="8" placeholder="Min. 8 karakter" autocomplete="new-password" required><button class="field-action" type="button" data-password-toggle="register-password" aria-label="Tampilkan password"><i class="bi bi-eye" aria-hidden="true"></i></button></div></div>
                        <div class="register-field"><label class="form-label" for="register-confirm">Konfirmasi password</label><input id="register-confirm" class="form-control" type="password" minlength="8" placeholder="Ulangi password" data-match="#register-password" autocomplete="new-password" required></div>
                    </div>
                    <div class="form-check auth-consent"><input class="form-check-input" type="checkbox" value="1" id="consent" required><label class="form-check-label" for="consent">Saya menyetujui ketentuan layanan dan kebijakan privasi.</label></div>
                    <button class="btn btn-primary auth-submit" type="submit">Buat akun <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                </form>
                <p class="auth-switch">Sudah memiliki akun? <button type="button" class="text-link" data-auth-switch="login">Masuk</button></p>
            </div>

            <footer class="auth-footer"><span>© <?= date('Y') ?> ANTRIAN SEHAT</span><span>Privasi &nbsp;·&nbsp; Bantuan</span></footer>
        </div>
    </section>
</div>