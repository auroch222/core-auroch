<?php

namespace auroch\phpmvc;

abstract class Model
{
    public const RULE_REQUIRED = 'required';
    public const RULE_EMAIL = 'email';
    public const RULE_MIN = 'min';
    public const RULE_MAX = 'max';
    public const RULE_MATCH = 'match';

    public const RULE_UNIQUE = 'unique';


    public function loadData($data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    abstract public function rules(): array;

    public array $errors = [];

    public function labels(): array
    {
        return [];
    }

    public function getLabel($attribute)
    {
        return $this->labels()[$attribute] ?? $attribute;
    }

    public function validate(): bool
    {
        foreach ($this->rules() as $attribute => $rules) {
            $value = $this->$attribute;

            foreach ($rules as $rule) {
                $ruleName = $rule;
                if (!is_string($rule)) {
                    $ruleName = $rule[0];
                }

                if ($ruleName === self::RULE_REQUIRED && !$value) {
                    $this->addErrorForRule($attribute, self::RULE_REQUIRED);
                }

                if ($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addErrorForRule($attribute, self::RULE_EMAIL);
                }

                if ($ruleName === self::RULE_MIN && strlen($value) < $rule['min']) {
                    $this->addErrorForRule($attribute, self::RULE_MIN, $rule);
                }

                if ($ruleName === self::RULE_MAX && strlen($value) > $rule['max']) {
                    $this->addErrorForRule($attribute, self::RULE_MAX, $rule);
                }

                if ($ruleName === self::RULE_MATCH && $value !== $this->{$rule['match']}) {
                    $this->addErrorForRule($attribute, self::RULE_MATCH, $rule);
                }

                if ($ruleName === self::RULE_UNIQUE) {
                    $className = $rule['class'];
                    $uniqueAttr = $rule['attribute'] ?? $attribute;
                    $tableName = $className::tableName();
                    $statement = Application::$app->db->prepare(
                        "SELECT * FROM $tableName where $uniqueAttr = :attr"
                    );
                    $statement->bindValue(':attr', $value);
                    $statement->execute();
                    $record = $statement->fetchObject();
                    var_dump($record);
                    if ($record) {
                        $this->addErrorForRule($attribute, self::RULE_UNIQUE, [
                            'field' => $this->getLabel($attribute),
                        ]);
                    }
                }

            }
        }

        return empty($this->errors);
    }

    private function addErrorForRule(string $attribute, string $rule, array $params = []): void
    {
        $message = $this->errorMessages()[$rule] ?? '';


        foreach ($params as $key => $value) {
            $message = str_replace(":{$key}", $value, $message);
        }

        $this->errors[$attribute][] = str_replace(":attribute", $attribute, $message);
    }

    public function addError(string $attribute, string $message): void
    {
        $this->errors[$attribute][] = $message;
    }


    public function errorMessages(): array
    {
        return [
            self::RULE_REQUIRED => 'The :attribute field is required.',
            self::RULE_EMAIL => 'The :attribute must be a valid email address.',
            self::RULE_MIN => 'The :attribute must be at least :min.',
            self::RULE_MAX => 'The :attribute may not be greater than :max.',
            self::RULE_MATCH => 'this :attribute field must be same as :match.',
            self::RULE_UNIQUE => 'Record with this :attribute already exists.',
        ];
    }

    public function hasError(string $attribute): bool
    {
        return isset($this->errors[$attribute]);
    }

    public function getFirstError($attribute)
    {
        return $this->errors[$attribute][0] ?? false;
    }
}