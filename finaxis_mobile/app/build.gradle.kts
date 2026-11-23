import java.util.Properties

plugins {
    alias(libs.plugins.android.application)
}

// Endereço da API PHP. Cada pessoa coloca o IP do próprio PC no arquivo
// local.properties (que não vai para o Git), por exemplo:
//   finaxis.apiUrl=http://192.168.0.15/sistema_financeiro/api/
val localProperties = Properties().apply {
    val file = rootProject.file("local.properties")
    if (file.exists()) file.inputStream().use { load(it) }
}
val apiUrl: String = localProperties.getProperty(
    "finaxis.apiUrl",
    "http://192.168.0.10/sistema_financeiro/api/"
)

android {
    namespace = "com.finaxis.finaxis_mobile"
    compileSdk {
        version = release(37)
    }

    defaultConfig {
        applicationId = "com.finaxis.finaxis_mobile"
        minSdk = 26
        targetSdk = 37
        versionCode = 1
        versionName = "1.0"

        testInstrumentationRunner = "androidx.test.runner.AndroidJUnitRunner"

        buildConfigField("String", "API_BASE_URL", "\"$apiUrl\"")
    }

    buildTypes {
        release {
            optimization {
                enable = true
                packageScope = setOf("androidx.**", "kotlin.**", "kotlinx.**")
            }
        }
    }
    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_11
        targetCompatibility = JavaVersion.VERSION_11
    }

    buildFeatures {
        viewBinding = true
        buildConfig = true
    }
}

dependencies {
    implementation(libs.androidx.activity.ktx)
    implementation(libs.androidx.appcompat)
    implementation(libs.androidx.constraintlayout)
    implementation(libs.androidx.core.ktx)
    implementation(libs.material)

    // Rede: Retrofit faz as chamadas HTTP e o Gson converte o JSON em classes Kotlin
    implementation(libs.retrofit)
    implementation(libs.retrofit.converter.gson)
    implementation(libs.okhttp.logging)

    // Guarda o token JWT no aparelho
    implementation(libs.androidx.datastore.preferences)

    // lifecycleScope e ViewModel (coroutines nas telas)
    implementation(libs.androidx.lifecycle.runtime.ktx)
    implementation(libs.androidx.lifecycle.viewmodel.ktx)

    // Listas (RecyclerView) e "puxar para atualizar"
    implementation(libs.androidx.recyclerview)
    implementation(libs.androidx.swiperefreshlayout)
    testImplementation(libs.junit)
    androidTestImplementation(libs.androidx.espresso.core)
    androidTestImplementation(libs.androidx.junit)
}