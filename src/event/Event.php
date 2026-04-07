<?php

declare(strict_types=1);

namespace dbschemix\core\event;

enum Event: string
{
    case InitializationError = 'initialization-error-event';

    case ConnectionError = 'connection-error-event';

    case ConfigurationError = 'configuration-error-event';

    case FilesystemError = 'filesystem-error-event';

    case FilesystemNotice = 'filesystem-notice-event';

    case MigrateSuccess = 'migrate-success-event';

    case MigrateError = 'migrate-error-event';
}
