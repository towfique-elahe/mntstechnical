<?php get_header(); ?>

<main id="main-content" class="site-main full-width">
    <?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
    <div class="elementor-full-width">
        <?php the_content(); ?>
    </div>
    <?php endwhile; ?>
    <?php endif; ?>
</main>

<?php get_footer(); ?>