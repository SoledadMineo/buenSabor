export default class ArticuloAdmin {
  id?: number;

  // comunes
  denominacion: string = '';
  precioVenta: number = 0;
  tipo: 'insumo' | 'manufacturado' = 'insumo';
  // insumo
  stock?: number;
  unidadMedidaId?: number;

  // manufacturado
  tiempoEstimado?: number;
  descripcion?: string;
}
