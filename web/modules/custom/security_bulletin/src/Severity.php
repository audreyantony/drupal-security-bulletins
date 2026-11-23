<?php

declare(strict_types=1);

namespace Drupal\security_bulletin;

/**
 * SEVERITY LEVELS OF A BULLETIN.
 *
 * THE BACKING VALUES MATCH THE ALLOWED VALUES OF FIELD_SEVERITY.
 */
enum Severity: string {

  case None = 'none';
  case Low = 'low';
  case Medium = 'medium';
  case High = 'high';
  case Critical = 'critical';

}
