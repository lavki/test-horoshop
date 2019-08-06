Реализовать с помощью PHP схему просчёта цен и скидок.  
Есть 4 сущности :  
Категория  
Продукт  
Цена  
Скидка  

У каждой категории есть название и уникальный идентификатор  
У каждого продукта: название, цена, категория и уникальный идентификатор.  
У цены есть свойства: сумма и валюта(4 типа : UAH, USD, EUR, RUB).  
У скидок есть 2 типа : абсолютная скидка(-100грн) и относительная(-15%).  
Так же скидка может быть применима как к самому продукту, так и ко всей категории продуктов.  

Нужно сделать выгрузку продуктов и агрегирования всех свойств связанных с ним.  
Валюта в которой стоит выгружать должна зависеть от переданного параметра в агрегатор/сервис.  
Т.е. выгружать стоит в формате:  

```
{
  "items" : [
    {
      "id": "unique-product-hash-1",
      "title": "iPhone X",
      "category": {
        "id": "unique-category-hash-1",
        "title": "Apple"
      },
      "price": {
        "amount": 200.0,
        "discounted_amount": 183.0,
        "currency": "UAH",
        "discount": {
          "type": "absolute",
          "value": 17,
          "relation": "product"
        }
      }
    },
    {
      "id": "unique-product-hash-2",
      "title": "Samsung Galaxy S10",
      "category": {
        "id": "unique-category-hash-2",
        "title": "Samsung"
      },
      "price": {
        "amount": 100.0,
        "discounted_amount": 90.0,
        "currency": "UAH",
        "discount": {
          "type": "percent",
          "value": 10,
          "relation": "category"
        }
      }
    },
    {
      "id": "unique-product-hash-2",
      "title": "Samsung Galaxy A20",
      "category": {
        "id": "unique-category-hash-2",
        "title": "Samsung"
      },
      "price": {
        "amount": 80.0,
        "discounted_amount": 72.0,
        "currency": "UAH",
        "discount": {
          "type": "percent",
          "value": 10,
          "relation": "category"
        }
      }
    }
  ],
  "perPage": 20,
  "pages": 10,
  "page" : 1
}
```

Не нужно делать : серверную часть, endpoint-ы. Достаточно одного PHP и PHPUnit для тестов

Использовать можно : composer для зависимостей/тестирования  
Использовать нужно : OOP(все должно быть покрыто reusable классами придерживаясь 3 принципов ООП), SOLID, DRY, PHPUnit  
Использовать нельзя : фреймворки, готовые решения  

Бонус :  
Реализовать +1 тип скидок "оптовый" (цена уменьшается в зависимости от кол-ва товаров). Должна быть настраиваемая (от 10 товаров, от 100 товаров, от 1000 товаров и тд).
