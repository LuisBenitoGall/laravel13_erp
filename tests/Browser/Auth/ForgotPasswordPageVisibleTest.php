<?php

it('muestra la página de recuperar contraseña', function () {
    $page = visit('/forgot-password');

    $page->assertPathIs('/forgot-password')
        ->assertSee('Recuperar contraseña')
        ->assertSee('Te enviaremos un enlace para restablecerla.')
        ->assertSee('Enviar enlace')
        ->assertSee('Volver al login');
});
