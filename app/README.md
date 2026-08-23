# Application Architecture

## Actions
Business operations/use cases. One action should represent one meaningful operation.

## Data
DTOs and structured data objects.

## Enums
Application enums such as status, type, role, and state.

## Exceptions
Application-specific exceptions.

## Policies
Authorization rules for resources.

## Services
Reusable or complex domain/application logic.
Do not create services for trivial operations.

## Support
Shared infrastructure helpers and application support utilities.

## Models
Eloquent persistence models.

## Controllers / Livewire
Thin presentation layer. Validate input, authorize, invoke application logic, return the response.

## Rules

- Do not introduce repositories unless there is a concrete need.
- Do not put complex business logic in controllers or Livewire components.
- Do not create abstractions speculatively.
- Prefer the simplest architecture that satisfies the current feature.
