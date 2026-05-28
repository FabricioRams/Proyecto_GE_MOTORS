import { apiService } from './api'

export const diagnosticoService = {
  // Endpoint para crear un nuevo diagnóstico
  create: (data) => apiService.post('/diagnosticos', data),
  // Endpoint para obtener diagnósticos de una orden específica
  getByOrden: (ordenId) => apiService.get(`/diagnosticos/orden/${ordenId}`),
  // Endpoint para parsear una trama hexadecimal RAW
  parsearTramaHex: (tramaHex) => apiService.post('/diagnosticos/parsear-trama', { trama_hex: tramaHex })
}