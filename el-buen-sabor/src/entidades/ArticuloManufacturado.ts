import type { ImagenInsumo } from "./ImagenInsumo";

export default class ArticuloManufacturado{

        id:number = 0;
        denominacion:string = "";
        descripcion:string = "";
        precioVenta:number = 0;
        precioCosto:number = 0;
        tiempoEstimado:number = 0;
        rubro:string = "";
        categoria_id:number = 0;    
        imagen?:ImagenInsumo | null;
}