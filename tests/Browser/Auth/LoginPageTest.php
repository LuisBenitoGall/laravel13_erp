<?php

it('muestra el formulario de login', function (): void {
    $page = visit('/login');

    $page->assertSee('Entrar')
        ->assertSee('Acceder');
});
