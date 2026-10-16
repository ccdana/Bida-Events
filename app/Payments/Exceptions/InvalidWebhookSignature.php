<?php

namespace App\Payments\Exceptions;

use RuntimeException;

/** El aviso no trae una firma válida de la pasarela: no se guarda ni se aplica. */
class InvalidWebhookSignature extends RuntimeException {}
