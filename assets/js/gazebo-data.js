// Bu dosya, HTML'deki <script src="gazebofiyat.js"></script> etiketi ile yüklenecektir.
// Verileri doğrudan global bir değişkene atayarak JS'e aktarır.

const globalFiyatData = {
  "fiyat_tablolari": {
    // Ürün Boyutuna Özel İskelet ve Aksesuar Fiyatları
    "2x2": {
      "genislik": "2m",
      "iskelet_aksesuar": {
        "30luk_celik": 2300.00,
        "40luk_celik": 0,
        "40lik_aluminyum": 0,
        "52lik_aluminyum": 0,
        "canta": 430.00,
        "oluk_30x200": 400.00
      }
    },
    "2x3": {
      "genislik": "3m",
      "iskelet_aksesuar": {
        "30luk_celik": 3000.00,
        "40luk_celik": 0,
        "40lik_aluminyum": 0,
        "52lik_aluminyum": 0,
        "canta": 430.00,
        "oluk_30x200": 400.00
      }
    },
    "3x3": {
      "genislik": "3m",
      "iskelet_aksesuar": {
        "30luk_celik": 2800.00,
        "40luk_celik": 4100.00,
        "40lik_aluminyum": 12500.00,
        "52lik_aluminyum": 14500.00,
        "canta": 430.00,
        "oluk_30x300": 500.00
      }
    },
    "3x4.5": {
      "genislik": "4.5m",
      "iskelet_aksesuar": {
        "30luk_celik": 0,
        "40luk_celik": 5800.00,
        "40lik_aluminyum": 13000.00,
        "52lik_aluminyum": 17500.00,
        "canta": 430.00,
        "oluk_30x450": 620.00
      }
    },
    "3x6": {
      "genislik": "6m",
      "iskelet_aksesuar": {
        "30luk_celik": 0,
        "40luk_celik": 7800.00,
        "40lik_aluminyum": 18000.00,
        "52lik_aluminyum": 33000.00,
        "canta": 500.00,
        "oluk_30x600": 620.00
      }
    },
    "4x4": {
      "genislik": "4m",
      "iskelet_aksesuar": {
        "30luk_celik": 0,
        "40luk_celik": 7250.00,
        "40lik_aluminyum": 0,
        "52lik_aluminyum": 22000.00,
        "canta": 600.00,
        "oluk_40x400": 720.00
      }
    },
    "4x6": {
      "genislik": "6m",
      "iskelet_aksesuar": {
        "30luk_celik": 0,
        "40luk_celik": 12000.00,
        "40lik_aluminyum": 0,
        "52lik_aluminyum": 23500.00,
        "canta": 600.00,
        "oluk_40x600": 750.00
      }
    },
    "4x8": {
      "genislik": "8m",
      "iskelet_aksesuar": {
        "30luk_celik": 0,
        "40luk_celik": 13000.00,
        "40lik_aluminyum": 0,
        "52lik_aluminyum": 26000.00,
        "canta": 600.00,
        "oluk_40x800": 900.00
      }
    }
  },

  // Genişlik ve Kumaşa Göre Tente/Duvar Fiyatları
  "genislik_bazli_fiyatlar": {
    "2m": {
      "690_denye": {
        "cati_sacak": 1800.00,
        "tam_duvar": 950.00,
        "yarim_duvar": 850.00,
        "seffaf_duvar": 1000.00,
        "fermuarli_duvar": 1065.00,
        "sineklik_duvar": 1000.00,
        "kapili_duvar": 1220.00,
        "sineklikli_kapi": 1620.00,
        "kapi_seffaf_pencere": 1380.00,
        "sineklikli_kapi_seffaf_perde": 1920.00,
        "seffaf_sineklik_perde_kapi": 2040.00,
        "seffaf_camli_duvar": 1200.00,
        "seffaf_camli_perdeli_duvar": 1320.00,
        "seffaf_sineklik_perdeli_camli_duvar": 1440.00
      },
      "200_oxford": {
        "cati_sacak": 2400.00,
        "tam_duvar": 1200.00,
        "yarim_duvar": 950.00,
        "seffaf_duvar": 1000.00,
        "fermuarli_duvar": 1250.00,
        "sineklik_duvar": 1000.00,
        "kapili_duvar": 1440.00,
        "sineklikli_kapi": 0,
        "kapi_seffaf_pencere": 1860.00,
        "sineklikli_kapi_seffaf_perde": 1860.00,
        "seffaf_sineklik_perde_kapi": 2160.00,
        "seffaf_camli_duvar": 1380.00,
        "seffaf_camli_perdeli_duvar": 1500.00,
        "seffaf_sineklik_perdeli_camli_duvar": 1620.00
      },
      "dijital_baski": {
        "cati_sacak": 4900.00,
        "tam_duvar": 1500.00,
        "yarim_duvar": 1050.00,
        "seffaf_duvar": 1000.00,
        "fermuarli_duvar": 1500.00,
        "sineklik_duvar": 0,
        "kapili_duvar": 1560.00,
        "sineklikli_kapi": 0,
        "kapi_seffaf_pencere": 0,
        "sineklikli_kapi_seffaf_perde": 0,
        "seffaf_sineklik_perde_kapi": 0,
        "seffaf_camli_duvar": 0,
        "seffaf_camli_perdeli_duvar": 0,
        "seffaf_sineklik_perdeli_camli_duvar": 0
      }
    },
    "3m": {
      "690_denye": {
        "cati_sacak": 2650.00,
        "tam_duvar": 1850.00,
        "yarim_duvar": 800.00,
        "seffaf_duvar": 1420.00,
        "fermuarli_duvar": 1300.00,
        "sineklik_duvar": 1420.00,
        "kapili_duvar": 1420.00,
        "sineklikli_kapi": 1650.00,
        "kapi_seffaf_pencere": 1650.00,
        "sineklikli_kapi_seffaf_perde": 1900.00,
        "seffaf_sineklik_perde_kapi": 2370.00,
        "seffaf_camli_duvar": 1560.00,
        "seffaf_camli_perdeli_duvar": 1690.00,
        "seffaf_sineklik_perdeli_camli_duvar": 1800.00
      },
      "200_oxford": {
        "cati_sacak": 3350.00,
        "tam_duvar": 1560.00,
        "yarim_duvar": 900.00,
        "seffaf_duvar": 1800.00,
        "fermuarli_duvar": 1680.00,
        "sineklik_duvar": 1800.00,
        "kapili_duvar": 1800.00,
        "sineklikli_kapi": 2050.00,
        "kapi_seffaf_pencere": 2050.00,
        "sineklikli_kapi_seffaf_perde": 2280.00,
        "seffaf_sineklik_perde_kapi": 2760.00,
        "seffaf_camli_duvar": 2000.00,
        "seffaf_camli_perdeli_duvar": 2100.00,
        "seffaf_sineklik_perdeli_camli_duvar": 2220.00
      },
      "dijital_baski": {
        "cati_sacak": 6200.00,
        "tam_duvar": 2250.00,
        "yarim_duvar": 1300.00,
        "seffaf_duvar": 0,
        "fermuarli_duvar": 0,
        "sineklik_duvar": 0,
        "kapili_duvar": 2160.00,
        "sineklikli_kapi": 0,
        "kapi_seffaf_pencere": 0,
        "sineklikli_kapi_seffaf_perde": 0,
        "seffaf_sineklik_perde_kapi": 0,
        "seffaf_camli_duvar": 0,
        "seffaf_camli_perdeli_duvar": 0,
        "seffaf_sineklik_perdeli_camli_duvar": 0
      }
    },
    "4m": {
      "690_denye": {
        "cati_sacak": 7000.00,
        "tam_duvar": 1600.00,
        "yarim_duvar": 1400.00,
        "seffaf_duvar": 1900.00,
        "fermuarli_duvar": 1700.00,
        "sineklik_duvar": 1900.00,
        "kapili_duvar": 1850.00,
        "sineklikli_kapi": 2350.00,
        "kapi_seffaf_pencere": 2200.00,
        "sineklikli_kapi_seffaf_perde": 2700.00,
        "seffaf_sineklik_perde_kapi": 2800.00,
        "seffaf_camli_duvar": 1850.00,
        "seffaf_camli_perdeli_duvar": 2000.00,
        "seffaf_sineklik_perdeli_camli_duvar": 2200.00
      },
      "200_oxford": {
        "cati_sacak": 7300.00,
        "tam_duvar": 1900.00,
        "yarim_duvar": 1500.00,
        "seffaf_duvar": 2250.00,
        "fermuarli_duvar": 2050.00,
        "sineklik_duvar": 2250.00,
        "kapili_duvar": 2150.00,
        "sineklikli_kapi": 2650.00,
        "kapi_seffaf_pencere": 2500.00,
        "sineklikli_kapi_seffaf_perde": 3100.00,
        "seffaf_sineklik_perde_kapi": 3240.00,
        "seffaf_camli_duvar": 2150.00,
        "seffaf_camli_perdeli_duvar": 2250.00,
        "seffaf_sineklik_perdeli_camli_duvar": 2500.00
      },
      "dijital_baski": {
        "cati_sacak": 13000.00,
        "tam_duvar": 2600.00,
        "yarim_duvar": 2250.00,
        "seffaf_duvar": 0,
        "fermuarli_duvar": 3100.00,
        "sineklik_duvar": 0,
        "kapili_duvar": 3200.00,
        "sineklikli_kapi": 0,
        "kapi_seffaf_pencere": 0,
        "sineklikli_kapi_seffaf_perde": 0,
        "seffaf_sineklik_perde_kapi": 0,
        "seffaf_camli_duvar": 0,
        "seffaf_camli_perdeli_duvar": 0,
        "seffaf_sineklik_perdeli_camli_duvar": 0
      }
    },
    "4.5m": {
      "690_denye": {
        "cati_sacak": 3900.00,
        "tam_duvar": 1850.00,
        "yarim_duvar": 1800.00,
        "seffaf_duvar": 2250.00,
        "fermuarli_duvar": 1950.00,
        "sineklik_duvar": 2250.00,
        "kapili_duvar": 2050.00,
        "sineklikli_kapi": 2300.00,
        "kapi_seffaf_pencere": 2300.00,
        "sineklikli_kapi_seffaf_perde": 2600.00,
        "seffaf_sineklik_perde_kapi": 2700.00,
        "seffaf_camli_duvar": 2550.00,
        "seffaf_camli_perdeli_duvar": 2750.00,
        "seffaf_sineklik_perdeli_camli_duvar": 3000.00
      },
      "200_oxford": {
        "cati_sacak": 4800.00,
        "tam_duvar": 2100.00,
        "yarim_duvar": 2100.00,
        "seffaf_duvar": 2900.00,
        "fermuarli_duvar": 2250.00,
        "sineklik_duvar": 2900.00,
        "kapili_duvar": 2250.00,
        "sineklikli_kapi": 2800.00,
        "kapi_seffaf_pencere": 2600.00,
        "sineklikli_kapi_seffaf_perde": 3250.00,
        "seffaf_sineklik_perde_kapi": 3100.00,
        "seffaf_camli_duvar": 2800.00,
        "seffaf_camli_perdeli_duvar": 3050.00,
        "seffaf_sineklik_perdeli_camli_duvar": 3300.00
      },
      "dijital_baski": {
        "cati_sacak": 9000.00,
        "tam_duvar": 3250.00,
        "yarim_duvar": 2250.00,
        "seffaf_duvar": 0,
        "fermuarli_duvar": 2760.00,
        "sineklik_duvar": 0,
        "kapili_duvar": 3350.00,
        "sineklikli_kapi": 0,
        "kapi_seffaf_pencere": 0,
        "sineklikli_kapi_seffaf_perde": 0,
        "seffaf_sineklik_perde_kapi": 0,
        "seffaf_camli_duvar": 0,
        "seffaf_camli_perdeli_duvar": 0,
        "seffaf_sineklik_perdeli_camli_duvar": 0
      }
    },
    "6m": {
      "690_denye": {
        "cati_sacak": 8500.00,
        "tam_duvar": 2600.00,
        "yarim_duvar": 2100.00,
        "seffaf_duvar": 2700.00,
        "fermuarli_duvar": 2450.00,
        "sineklik_duvar": 2700.00,
        "kapili_duvar": 2650.00,
        "sineklikli_kapi": 3100.00,
        "kapi_seffaf_pencere": 3100.00,
        "sineklikli_kapi_seffaf_perde": 3800.00,
        "seffaf_sineklik_perde_kapi": 3950.00,
        "seffaf_camli_duvar": 2650.00,
        "seffaf_camli_perdeli_duvar": 2750.00,
        "seffaf_sineklik_perdeli_camli_duvar": 3150.00
      },
      "200_oxford": {
        "cati_sacak": 9500.00,
        "tam_duvar": 2750.00,
        "yarim_duvar": 1900.00,
        "seffaf_duvar": 3200.00,
        "fermuarli_duvar": 2820.00,
        "sineklik_duvar": 3300.00,
        "kapili_duvar": 3050.00,
        "sineklikli_kapi": 3550.00,
        "kapi_seffaf_pencere": 3550.00,
        "sineklikli_kapi_seffaf_perde": 4350.00,
        "seffaf_sineklik_perde_kapi": 4550.00,
        "seffaf_camli_duvar": 3000.00,
        "seffaf_camli_perdeli_duvar": 3100.00,
        "seffaf_sineklik_perdeli_camli_duvar": 3450.00
      },
      "dijital_baski": {
        "cati_sacak": 15750.00,
        "tam_duvar": 4200.00,
        "yarim_duvar": 3400.00,
        "seffaf_duvar": 0,
        "fermuarli_duvar": 4900.00,
        "sineklik_duvar": 0,
        "kapili_duvar": 5150.00,
        "sineklikli_kapi": 0,
        "kapi_seffaf_pencere": 0,
        "sineklikli_kapi_seffaf_perde": 0,
        "seffaf_sineklik_perde_kapi": 0,
        "seffaf_camli_duvar": 0,
        "seffaf_camli_perdeli_duvar": 0,
        "seffaf_sineklik_perdeli_camli_duvar": 0
      }
    },
    "8m": {
      "690_denye": {
        "cati_sacak": 10000.00,
        "tam_duvar": 3500.00,
        "yarim_duvar": 2800.00,
        "seffaf_duvar": 3790.00,
        "fermuarli_duvar": 3500.00,
        "sineklik_duvar": 3750.00,
        "kapili_duvar": 3750.00,
        "sineklikli_kapi": 4200.00,
        "kapi_seffaf_pencere": 4200.00,
        "sineklikli_kapi_seffaf_perde": 4700.00,
        "seffaf_sineklik_perde_kapi": 4900.00,
        "seffaf_camli_duvar": 3850.00,
        "seffaf_camli_perdeli_duvar": 4050.00,
        "seffaf_sineklik_perdeli_camli_duvar": 4450.00
      },
      "200_oxford": {
        "cati_sacak": 15500.00,
        "tam_duvar": 3900.00,
        "yarim_duvar": 1950.00,
        "seffaf_duvar": 4500.00,
        "fermuarli_duvar": 4000.00,
        "sineklik_duvar": 4500.00,
        "kapili_duvar": 4250.00,
        "sineklikli_kapi": 4750.00,
        "kapi_seffaf_pencere": 4750.00,
        "sineklikli_kapi_seffaf_perde": 5200.00,
        "seffaf_sineklik_perde_kapi": 5200.00,
        "seffaf_camli_duvar": 4350.00,
        "seffaf_camli_perdeli_duvar": 4600.00,
        "seffaf_sineklik_perdeli_camli_duvar": 4850.00
      },
      "dijital_baski": {
        "cati_sacak": 22000.00,
        "tam_duvar": 5600.00,
        "yarim_duvar": 4500.00,
        "seffaf_duvar": 0,
        "fermuarli_duvar": 6800.00,
        "sineklik_duvar": 0,
        "kapili_duvar": 7050.00,
        "sineklikli_kapi": 0,
        "kapi_seffaf_pencere": 0,
        "sineklikli_kapi_seffaf_perde": 0,
        "seffaf_sineklik_perde_kapi": 0,
        "seffaf_camli_duvar": 6750.00,
        "seffaf_camli_perdeli_duvar": 0,
        "seffaf_sineklik_perdeli_camli_duvar": 0
      }
    }
  }
};