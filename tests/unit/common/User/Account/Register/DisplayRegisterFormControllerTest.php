<?php
/**
 * Copyright (c) Enalean, 2023 - Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Tuleap\User\Account\Register;

use Tuleap\Event\Dispatchable;
use Tuleap\Layout\BaseLayout;
use Tuleap\Request\ForbiddenException;
use Tuleap\Test\Builders\HTTPRequestBuilder;
use Tuleap\Test\PHPUnit\TestCase;
use Tuleap\Test\Stubs\EventDispatcherStub;
use Tuleap\User\Account\RegistrationGuardEvent;

final class DisplayRegisterFormControllerTest extends TestCase
{
    public function testPasswordIsNeededByDefault(): void
    {
        $form_displayer = IDisplayRegisterFormStub::buildSelf();

        $controller = new DisplayRegisterFormController(
            $form_displayer,
            EventDispatcherStub::withIdentityCallback(),
        );
        $controller->process(
            HTTPRequestBuilder::get()->build(),
            $this->createMock(BaseLayout::class),
            [],
        );

        self::assertTrue($form_displayer->hasBeenDisplayed());
        self::assertFalse($form_displayer->isAdmin());
        self::assertTrue($form_displayer->isPasswordNeeded());
    }

    public function testWhenPasswordIsNotNeeded(): void
    {
        $form_displayer = IDisplayRegisterFormStub::buildSelf();

        $controller = new DisplayRegisterFormController(
            $form_displayer,
            EventDispatcherStub::withCallback(
                static function (Dispatchable $event): object {
                    if ($event instanceof BeforeUserRegistrationEvent) {
                        $event->noNeedForPassword();
                    }

                    return $event;
                }
            ),
        );
        $controller->process(
            HTTPRequestBuilder::get()->build(),
            $this->createMock(BaseLayout::class),
            [],
        );

        self::assertTrue($form_displayer->hasBeenDisplayed());
        self::assertFalse($form_displayer->isAdmin());
        self::assertFalse($form_displayer->isPasswordNeeded());
    }

    public function testRejectWhenRegistrationIsNotPossible(): void
    {
        $this->expectException(ForbiddenException::class);

        $form_displayer = IDisplayRegisterFormStub::buildSelf();

        $controller = new DisplayRegisterFormController(
            $form_displayer,
            EventDispatcherStub::withCallback(
                static function (Dispatchable $event): object {
                    if ($event instanceof RegistrationGuardEvent) {
                        $event->disableRegistration();
                    }

                    return $event;
                }
            ),
        );
        $controller->process(
            HTTPRequestBuilder::get()->build(),
            $this->createMock(BaseLayout::class),
            [],
        );

        self::assertFalse($form_displayer->hasBeenDisplayed());
    }
}
