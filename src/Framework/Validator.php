<?php

declare(strict_types=1);

namespace Framework;

use Framework\Contracts\RuleInterface;
use Framework\Exceptions\ValidationException;

class Validator
{
    private array $rules = [];

    public function add(string $alias, RuleInterface $rule)
    {
        $this->rules[$alias] = $rule;
    }

    public function validate(array $formData, array $fields)
    {
        $errors = [];

        foreach ($fields as $fieldName => $rules) {
            foreach ($rules as $rule) {
                // First detects if arguments are being sent to the rule (marked by a colon)
                $ruleParams = [];
                if (str_contains($rule, ":")) {
                    [$rule, $ruleParams] = explode(":", $rule);
                    // Check for multiple arguments
                    $ruleParams = explode(",", $ruleParams);
                }
                // If the data in the field passes, then move on to next field.
                $ruleValidator = $this->rules[$rule];
                if ($ruleValidator->validate($formData, $fieldName, $ruleParams)) {
                    continue;
                }
                // If the data in the field does not pass, create error message.
                $errors[$fieldName][] = $ruleValidator->getMessage($formData, $fieldName, $ruleParams);
            }
        }
        if (count($errors)) throw new ValidationException($errors);
    }
}
