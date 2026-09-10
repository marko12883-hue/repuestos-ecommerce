<?php
/**
 * Plantilla de respaldo para páginas y entradas.
 */
get_header(); ?>

<main class="contenido">
    <div class="contenedor">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article>
                <h1><?php the_title(); ?></h1>
                <?php the_content(); ?>
            </article>
        <?php endwhile; endif; ?>
    </div>
</main>

<?php get_footer(); ?>
