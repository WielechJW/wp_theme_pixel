<?php
/**
 * Template Name: Kontakt
 * Template Post Type: page
 *
 * @package pixel
 */

get_header();

$status        = isset( $_GET['contact_status'] ) ? sanitize_key( wp_unslash( $_GET['contact_status'] ) ) : '';
$admin_email   = sanitize_email( (string) get_option( 'admin_email' ) );
$projects_page = get_page_by_path( 'realizacje' );

if ( ! $projects_page instanceof WP_Post ) {
	$projects_page = get_page_by_path( 'projekty' );
}

$projects_url = $projects_page instanceof WP_Post ? get_permalink( $projects_page ) : home_url( '/#realizacje' );
?>

	<main id="primary" class="site-main site-main--front site-main--contact">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<section class="contact-hero section-cream">
				<div class="contact-hero__inner">
					<p class="contact-eyebrow"><?php esc_html_e( 'Kontakt', 'pixel' ); ?></p>
					<h1 class="contact-hero__title"><?php the_title(); ?></h1>
					<p class="contact-hero__lead">
						<?php esc_html_e( 'Opisz, czego potrzebujesz, do czego ma służyć projekt i jaki masz termin. Wrócimy z konkretną propozycją działania.', 'pixel' ); ?>
					</p>
				</div>
			</section>

			<section class="contact-layout section-white">
				<div class="contact-layout__inner">
					<div class="contact-grid">
						<div class="contact-details">
							<div class="contact-card card">
								<p class="contact-card__eyebrow"><?php esc_html_e( 'Napisz do nas', 'pixel' ); ?></p>
								<h2 class="contact-card__title"><?php esc_html_e( 'Najwygodniej zacząć od krótkiej wiadomości.', 'pixel' ); ?></h2>
								<p>
									<?php esc_html_e( 'Im więcej konkretów podasz na starcie, tym szybciej będziemy mogli zaproponować sensowne rozwiązanie.', 'pixel' ); ?>
								</p>
							</div>

							<div class="contact-card card">
								<p class="contact-card__eyebrow"><?php esc_html_e( 'Dane kontaktowe', 'pixel' ); ?></p>
								<ul class="contact-list">
									<?php if ( '' !== $admin_email ) : ?>
										<li>
											<span class="contact-list__label"><?php esc_html_e( 'E-mail', 'pixel' ); ?></span>
											<a href="<?php echo esc_url( 'mailto:' . $admin_email ); ?>"><?php echo esc_html( $admin_email ); ?></a>
										</li>
									<?php endif; ?>
									<li>
										<span class="contact-list__label"><?php esc_html_e( 'Odpowiedź', 'pixel' ); ?></span>
										<span><?php esc_html_e( 'Zwykle w 1-2 dni robocze', 'pixel' ); ?></span>
									</li>
									<li>
										<span class="contact-list__label"><?php esc_html_e( 'Realizacje', 'pixel' ); ?></span>
										<a href="<?php echo esc_url( $projects_url ); ?>"><?php esc_html_e( 'Zobacz nasze projekty', 'pixel' ); ?></a>
									</li>
								</ul>
							</div>
						</div>

						<div class="contact-form-wrap card">
							<?php if ( 'success' === $status ) : ?>
								<div class="contact-notice contact-notice--success">
									<?php esc_html_e( 'Wiadomość została wysłana. Odezwiemy się najszybciej jak to możliwe.', 'pixel' ); ?>
								</div>
							<?php elseif ( 'error' === $status ) : ?>
								<div class="contact-notice contact-notice--error">
									<?php esc_html_e( 'Nie udało się wysłać wiadomości. Spróbuj ponownie albo napisz bezpośrednio na e-mail.', 'pixel' ); ?>
								</div>
							<?php endif; ?>

							<form class="contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
								<input type="hidden" name="action" value="pixel_contact_form_submit" />
								<?php wp_nonce_field( 'pixel_contact_form_submit', 'pixel_contact_nonce' ); ?>

								<p class="contact-form__row">
									<label class="contact-form__label" for="contact-name"><?php esc_html_e( 'Imię', 'pixel' ); ?></label>
									<input id="contact-name" name="contact_name" type="text" required />
								</p>

								<p class="contact-form__row">
									<label class="contact-form__label" for="contact-email"><?php esc_html_e( 'E-mail', 'pixel' ); ?></label>
									<input id="contact-email" name="contact_email" type="email" required />
								</p>

								<p class="contact-form__row">
									<label class="contact-form__label" for="contact-subject"><?php esc_html_e( 'Temat', 'pixel' ); ?></label>
									<input id="contact-subject" name="contact_subject" type="text" required />
								</p>

								<p class="contact-form__row">
									<label class="contact-form__label" for="contact-message"><?php esc_html_e( 'Wiadomość', 'pixel' ); ?></label>
									<textarea id="contact-message" name="contact_message" rows="7" required></textarea>
								</p>

								<p class="contact-form__trap" aria-hidden="true">
									<label for="contact-company"><?php esc_html_e( 'Firma', 'pixel' ); ?></label>
									<input id="contact-company" name="contact_company" type="text" tabindex="-1" autocomplete="off" />
								</p>

								<p class="contact-form__actions">
									<button type="submit"><?php esc_html_e( 'Wyślij wiadomość', 'pixel' ); ?></button>
								</p>
							</form>
						</div>
					</div>
				</div>
			</section>
			<?php
		endwhile;
		?>
	</main>

<?php
get_footer();
