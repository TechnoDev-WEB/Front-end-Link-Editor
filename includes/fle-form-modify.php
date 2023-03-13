<div class="fle-overlay"></div>

<div class="fle-modal">
  <a class="fle-edit-btn__wrapper">
    <div class="fle-edit-btn">
      <img src="<?php echo FLE_URL . 'assets/images/icons/pen-solid.svg'; ?>" alt="">
    </div>
  </a>

  <div class="fle-form__wrapper">
    <form id="fle-form" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
      <input type="hidden" name="action" value="fle_update_database_link">

      <img class="fle-form__close" src="<?php echo FLE_URL . 'assets/images/icons/xmark.svg'; ?>" alt="">
      <div class="fle-form__show-message"></div>
      <p class="fle-form__title">Editare</p>

      <div class="fle-form__field">
        <label for="fle-link_field">URL</label>
        <input type="text" name="fle-link_field" id="fle-link_field" placeholder="Link" required />
      </div>

      <div class="fle-form__field" id="fle-text">
        <label for="fle-link_text">Link Text</label>
        <input type="text" name="fle-link_text" id="fle-link_text" placeholder="Text"/>
      </div>

      <div class="fle-form__checkbox-field">
        <input type="checkbox" id="fle-target" name="fle-target"> 
        <label for="fle-target">Open the link in a new tab</label>
      </div>

      <div class="fle-form__field flex-row">
          <input type="hidden" name="fle_form_nonce" value="<?php echo wp_create_nonce('validateForm'); ?>" />
          <button id="fle-link_button"> Update </button>
          <div class="fle-loader"></div>
      </div>
    </form>
  </div>
</div>

