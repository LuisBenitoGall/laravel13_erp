<?php

it('module:audit lista entidades Admin sin fallar en dry-run de salida', function () {
    $this->artisan('module:audit')
        ->assertSuccessful();
});
