# Loose Challenge

## Vending Machine
- The goal of this program is to model a vending machine and the state it must maintain during its operation.
- The machine works like all vending machines: it takes money then gives you items. The vending machine accepts money in the form of 0.05, 0.10, 0.25 and 1.
- The current 3 primary items available are:
  - Water (0.65),
  - Juice(1.00),
  - Soda(1.50)
- Also user may hit the button “return coin” to get back the money they’ve entered so far, If you put more money in than the item price, you get the item and change back.

## Specification
### Valid set of actions on the vending machine are:
- 0.05, 0.10, 0.25, 1 - insert money
- Return Coin - returns all inserted money
- GET Water, GET Juice, GET Soda - select item
- SERVICE - a service person opens the machine and set the available change and how many items we have.

### Valid set of responses on the vending machine are:
- 0.05, 0.10, 0.25 - return coin
- Water, Juice, Soda - vend item

### Vending machine tracks the following state:
- Available items - each item has a count, a price and selector
- Available change - Number os coins available
- Currently inserted money

### Examples
```
Example 1: Buy Soda with exact change
1, 0.25, 0.25, GET-SODA
-> SODA

Example 2: Start adding money, but user ask for return coin
0.10, 0.10, RETURN-COIN
-> 0.10, 0.10

Example 3: Buy Water without exact change
1, GET-WATER
-> WATER, 0.25, 0.10

Example 4: Service that only adds change
1, 0.25, 0.25, SERVICE, DONE
-> OK

Example 5: Service with change and new products
1, 0.25, 0.25, SERVICE, SODA, WATER, JUICE, JUICE, DONE
-> OK
````

### Requirements
This project requires php 8.1 and composer installed. A Dockerized version will be supported soon.

### Run the tests
```
composer install
./vendor/bin/phpunit tests
```
