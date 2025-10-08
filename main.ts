// 1. Declara un array de números llamado 'misNumeros'.
//    La sintaxis para un array de números es: number[]
let misNumeros: number[] = [4, 8, 15, 16, 23, 42];


// 2. Declara una función llamada 'sumarArray'.
//    - Debe aceptar un parámetro que sea un array de números (ej: 'numeros').
//    - Debe devolver un único número (la suma total).
function sumarArray(num: number[]): number {

  // 3. Dentro de la función, crea una variable para guardar la suma.
  //    Esta variable se conoce como 'acumulador'. ¡Inicialízala en 0!
  let sumaTotal: number = 0;

  // 4. Recorre el array que recibiste como parámetro.
  //    Por cada 'numero' en el array, súmalo a 'sumaTotal'.
  //    Un bucle 'for...of' es perfecto para esto.
  for (const numero of misNumeros) {
    sumaTotal += numero;
  }

  // 5. Una vez que el bucle termina, devuelve la suma total.
  return sumaTotal;
}


// 6. Llama a la función con tu array y muestra el resultado.
let granTotal = sumarArray(misNumeros);
console.log('La suma de los números del array es: ' + granTotal);