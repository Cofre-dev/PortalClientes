async function getDatos() {

    //Async siempre devuelve una promesa
    //Await solo puede esperar a algo que sea una Promesa

    //Al iniciar la función
    console.log("1.- Vamos a obtener los datos")
    
    try {
        //Al escribir el método 'await' esta diciendo: espera a que el axios.get me retorne los datos de la API que ingrese
        const answer = await axios.get('https://api.ejemplo.com/datos'); 

        //Si la linea anterior retorna algún dato, se mostrará esta linea en la consola.
        console.log("2.- Petición exitosa: ", answer.data)

    } catch (error) {
        //En caso contrario, la petición fallará y mostrará este mensaje
        console.log("2.- Petición fallida: ", error)
    }

    //De todos modos, siempre se terminará la función
    console.log("2.- La funcion ha terminado")
}

// Función Login

async function login() {
    try {
        //path es la ruta implicita de la API (para evitar filtraciones de esta se concatena con variables de entorno)
        const response = await axios.post(path, {
            /*...*/
        })

        //Este codigo solo se ejecuta si la promesa de axios se cumplio

        //El localStorage almacena la llave temporal (accessToken) del usuario en el cache
        localStorage.setItem('accessTokem', response.data.access);
        //El router funciona como una especia de recepcionista, para hacer más rapido las transiciones de vistas
        router.push('/Inicio');

    } catch (error) {
        error.value = "Usuario o contraseña incorrecto";
    }
}


// hay una cuenta seguro => los conceptos son modificables





























