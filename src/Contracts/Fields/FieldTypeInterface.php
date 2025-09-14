<?php
/*
 * This file is part of Setka CMS.
 *
 * Copyright (c) 2025 Vitaliy Kamelin. All rights reserved.
 * Proprietary license. Unauthorized copying, modification or distribution
 * of this file, via any medium, is strictly prohibited without prior written permission.
 *
 * @package   Setka CMS
 * @version   1.0.0
 * @author    Vitaliy Kamelin <v.kamelin@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
 */

namespace Setka\Cms\Contracts\Fields;

interface FieldTypeInterface
{
    public function handle(): string;                 // 'text', 'number', 'date', 'relation', 'matrix' и т.д.
    
    public function configure(array $config): void;   // валидация/нормализация конфига
    
    public function validate(mixed $value): void;     // бросает исключение при несоответствии
    
    public function serialize(mixed $value): mixed;   // → значение для хранилища
    
    public function deserialize(mixed $raw): mixed;   // ← из хранилища в доменный тип
}
