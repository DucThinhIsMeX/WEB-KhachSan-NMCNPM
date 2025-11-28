class BaoCaoController {
    constructor(baoCaoService) {
        this.baoCaoService = baoCaoService;
    }

    // YC5: Lập báo cáo tháng (BM5)
    lapBaoCaoThang(thang, nam) {
        try {
            if (!thang || !nam) {
                throw new Error('Vui lòng chọn tháng và năm');
            }

            const result = this.baoCaoService.lapBaoCaoThang(
                parseInt(thang),
                parseInt(nam)
            );

            return result;
        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    layBaoCaoTheoNam(nam) {
        try {
            const baoCao = this.baoCaoService.layBaoCaoTheoNam(parseInt(nam));
            return {
                success: true,
                data: baoCao
            };
        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    xuatBaoCaoPDF(baoCao) {
        // Sẽ implement khi cần
        console.log('Xuất PDF báo cáo:', baoCao);
    }
}
