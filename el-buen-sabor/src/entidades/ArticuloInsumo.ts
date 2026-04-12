import type { ImagenInsumo } from "./ImagenInsumo";

export default class ArticuloInsumo{
    id:number = 0;
    denominacion:string = '';
    precioCompra:number = 0;
    precioVenta:number = 0;
    esParaElaborar:string = '';
    categoriaArtId:number = 0;
    imagenInsumoId:number = 0;
    unidadMedidaId:number = 0;
    padreId:number = 0;

    imagen?: ImagenInsumo | null;
}