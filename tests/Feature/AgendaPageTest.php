<?php

namespace Tests\Feature;

use Tests\TestCase;

class AgendaPageTest extends TestCase
{
    public function test_home_displays_the_calendar_workspace(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertViewIs('agenda')
            ->assertSee('Clinica Horizonte')
            ->assertSee('id="calendar"', false)
            ->assertSee('id="doctor-filter"', false)
            ->assertSee('id="patient-filter"', false)
            ->assertSee('id="appointment-dialog"', false)
            ->assertSee('id="detail-dialog"', false);
    }
}
