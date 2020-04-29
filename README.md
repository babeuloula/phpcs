# BaBeuloula PHP Code Sniffs

My own PHP code sniffs

## Installation

```bash
composer require --dev babeuloula/phpcs
```

```xml
<?xml version="1.0"?>
<ruleset>
    <rule ref="vendor/babeuloula/phpcs/BaBeuloula/ruleset.xml"/>
</ruleset>
```

## Coding standards

| Sniff |
|-------|
| [BaBeuloula.CodeAnalysis.Backslash](https://github.com/babeuloula/phpcs/blob/master/BaBeuloula/Sniffs/CodeAnalysis/BackslashSniff.php) |
| [BaBeuloula.CodeAnalysis.StrictTypes](https://github.com/babeuloula/phpcs/blob/master/BaBeuloula/Sniffs/CodeAnalysis/StrictTypesSniff.php) |
| [BaBeuloula.Uses.GroupUses](https://github.com/babeuloula/phpcs/blob/master/BaBeuloula/Sniffs/Uses/GroupUsesSniff.php) |
| [BaBeuloula.Functions.FunctionCallSignature](https://github.com/babeuloula/phpcs/blob/master/BaBeuloula/Sniffs/Functions/FunctionCallSignatureSniff.php) |
| [BaBeuloula.Properties.ConstantVisibility](https://github.com/babeuloula/phpcs/blob/master/BaBeuloula/Sniffs/Properties/ConstantVisibilitySniff.php) |

