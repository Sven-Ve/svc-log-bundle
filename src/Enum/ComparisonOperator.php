<?php

namespace Svc\LogBundle\Enum;

enum ComparisonOperator: string
{
  case EQUAL = '=';
  case GREATER_THAN = '>';
  case GREATER_THAN_OR_EQUAL = '>=';
  case LESS_THAN = '<';
  case LESS_THAN_OR_EQUA = '<=';
  case NOT_EQUAL = '!=';
}
