<?php

it('muestra el aviso legal placeholder', function () {
    $page = visit('/legal/notice');

    $page->assertPathIs('/legal/notice')
        ->assertSee('Aviso legal')
        ->assertSee('Contenido placeholder')
        ->assertSee('Volver al registro');
});

it('muestra los términos placeholder', function () {
    $page = visit('/legal/terms');

    $page->assertPathIs('/legal/terms')
        ->assertSee('Términos y condiciones')
        ->assertSee('Contenido placeholder')
        ->assertSee('Volver al registro');
});
