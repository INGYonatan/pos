/**
 * Hook de contador personalizado con integración DOM
 * 
 * Este hook proporciona un contador reactivo con funcionalidades básicas de incremento,
 * decremento, reset y actualización de valor. Implementa un patrón de observador
 * que permite suscribirse a cambios en el valor del contador y actualiza automáticamente
 * un elemento HTML con id="contador".
 * 
 * @param {number} initialValue - Valor inicial del contador (por defecto 0)
 * @returns {Object} Objeto con métodos para controlar el contador
 * 
 * Métodos disponibles:
 * - getCount(): Obtiene el valor actual del contador
 * - increment(): Incrementa el contador en 1
 * - decrement(): Decrementa el contador en 1
 * - reset(): Restaura el contador a su valor inicial
 * - setValue(newValue): Establece un valor específico al contador
 * - subscribe(callback): Se suscribe a cambios del contador, retorna función para desuscribirse
 * 
 * Ejemplo de uso:
 * const counter = useCounter(10);
 * counter.increment(); // Actualiza automáticamente el elemento #contador
 */
function useCounter(initialValue = 0) {
  let count = initialValue;
  const listeners = [];
  const contadorElement = document.getElementById('contador');

  function updateDOM() {
    if (contadorElement) {
      contadorElement.textContent = count;
    }
  }

  function getCount() {
    return count;
  }

  function increment() {
    count++;
    updateDOM();
    notifyListeners();
  }

  function decrement() {
    count--;
    updateDOM();
    notifyListeners();
  }

  function reset() {
    count = initialValue;
    updateDOM();
    notifyListeners();
  }

  function setValue(newValue) {
    count = newValue;
    updateDOM();
    notifyListeners();
  }

  function subscribe(callback) {
    listeners.push(callback);

    return () => {
      const index = listeners.indexOf(callback);
      if (index > -1) {
        listeners.splice(index, 1);
      }
    };
  }

  function notifyListeners() {
    listeners.forEach(callback => callback(count));
  }

  // Inicializar el DOM con el valor inicial
  updateDOM();

  return {
    getCount,
    increment,
    decrement,
    reset,
    setValue,
    subscribe
  };
}