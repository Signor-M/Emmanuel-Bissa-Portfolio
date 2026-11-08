<?php
try {
    $web3Stmt = $pdo->query("SELECT api_key FROM web3forms ORDER BY id ASC LIMIT 1");
    $web3Key = $web3Stmt->fetchColumn() ?: "94870937-409d-4a36-8e3f-d2fc2de72800";
} catch (Exception $e) {
    $web3Key = "94870937-409d-4a36-8e3f-d2fc2de72800";
}
?>
<section class="contact" id="contact">
    <h2 class="heading">Me contacter</h2>

    <form action="https://api.web3forms.com/submit" method="POST">
      <div class="input-group">
        <input type="hidden" name="access_key" value="<?php echo htmlspecialchars($web3Key); ?>" />
        <div class="input-box">
          <input type="text" name="name" placeholder="Nom complet" required />
          <input type="email" name="email" placeholder="Adresse e-mail" required />
        </div>
        <div class="input-box">
          <input type="tel" name="number" placeholder="Numéro de téléphone" required />
        </div>
      </div>

      <div class="input-group-2">
        <textarea name="message" cols="30" rows="10" placeholder="Votre message" required></textarea>
        <input type="submit" value="Envoyer le message" class="btn" />
      </div>
    </form>
</section>
