<?php

namespace enums;

enum HttpStatusCodes: int
{
	case UNAUTHORIZED = 401;
	case UNPROCESSABLE = 422;
	case INTERNAL = 500;
}
