export default class ArticuloAdmin {
  id?: number;

  // comunes
  denominacion: string = '';
  precioVenta: number = 0;
  tipo: 'insumo' | 'manufacturado' = 'insumo';
  // insumo
  esParaElaborar?: boolean;
  stock_total?: number | null;
  stock_bajo?: boolean;
  stock_posible?: number;
  deleted_at?: string | null;
  categoria?: { id: number; denominacion: string };
  unidad_medida?: { id: number; denominacion: string };
  stock?: number;
  unidadMedidaId?: number;

  // manufacturado
  tiempoEstimado?: number;
  descripcion?: string;
}
