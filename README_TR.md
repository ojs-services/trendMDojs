# OJS için TrendMD Öneri Aracı Eklentisi

[TrendMD](https://www.trendmd.com/) makale öneri aracını OJS 3.3+ dergi makale sayfalarına entegre eder.

Tema dosyalarında düzenleme gerektirmez — derginizin TrendMD tanımlayıcısını girin, eklenti gerisini halleder.

## Özellikler

- **Kolay Kurulum** — Derginizin TrendMD UUID'sini girin. Kod yapıştırma veya tema düzenleme gerekmez.
- **Otomatik Entegrasyon** — `trendmd.min.js` dosyasını doğru `data-trendmdconfig` yapılandırmasıyla sayfa başlığına yükler ve `#trendmd-suggestions` kapsayıcısını seçtiğiniz konuma yerleştirir.
- **Dergiye Özel Yapılandırma** — Her dergi bağlamı kendi TrendMD tanımlayıcısını saklar. Çoklu dergi kurulumlarını tam olarak destekler.
- **Yapılandırılabilir Konum** — Aracı özetin altına, kenar çubuğuna veya sayfa alt bilgisine yerleştirin.
- **UUID Doğrulama** — Kaydetmeden önce tanımlayıcı formatını kontrol eden doğrulama butonu.
- **Yer Tutucu Modu** — Tanımlayıcı yapılandırılmadığında araç konumunda görünür bir bildirim gösterilir. Böylece yöneticiler canlıya almadan önce yerleşimi doğrulayabilir.
- **Özel CSS Sınıfı** — Temaya özel stil için araç kapsayıcısına kendi sınıf adınızı ekleyin.
- **İki Dilli Arayüz** — İngilizce ve Türkçe.

## Gereksinimler

- OJS 3.3.0 veya üstü
- PHP 7.4 – 8.2
- Dergi tanımlayıcısına sahip bir [TrendMD](https://www.trendmd.com/) hesabı

## Kurulum

1. [GitHub Releases](https://github.com/ojs-services/trendMDojs/releases) sayfasından son sürümü indirin
2. **Ayarlar → Web Sitesi → Eklentiler → Yeni Eklenti Yükle** bölümüne gidin
3. `.tar.gz` dosyasını yükleyin
4. **Genel Eklentiler** altında eklentiyi etkinleştirin

Manuel kurulum için arşivi `plugins/generic/trendMDojs/` dizinine çıkarın.

## Yapılandırma

1. Eklentiyi etkinleştirin ve **Ayarlar**'a tıklayın
2. Derginizin **TrendMD Tanımlayıcısını** (UUID) girin
3. Formatı doğrulamak için **Tanımlayıcıyı Doğrula**'ya tıklayın
4. **Araç Konumu**'nu seçin
5. Kaydetmek için **Tamam**'a tıklayın

Geçerli bir tanımlayıcı kaydedildiğinde araç makale sayfalarında görünecektir.

## Nasıl Çalışır

| Bileşen | Yöneten |
|---------|---------|
| `<script defer src="trendmd.min.js" data-trendmdconfig="...">` | Eklenti (otomatik) |
| `<div id="trendmd-suggestions"></div>` | Eklenti (seçilen konumda) |
| Dergi UUID | Yönetici (tek seferlik kurulum) |

## Araç Konumları

| Seçenek | OJS Hook | Açıklama |
|---------|----------|----------|
| Makale Özeti | `Templates::Article::Main` | Özetin altında (varsayılan) |
| Makale Detayları | `Templates::Article::Details` | Kenar çubuğu alanı |
| Sayfa Alt Bilgisi | `Templates::Article::Footer::PageFooter` | Makale sayfasının en altı |

## Çoklu Dergi Desteği

OJS kurulumundaki her dergi kendi TrendMD tanımlayıcısını bağımsız olarak saklar. Her dergiyi kendi eklenti ayarları üzerinden yapılandırın.

## Uyumluluk

PHP 7.4, 8.0, 8.1 ve 8.2 üzerinde OJS 3.3.x ile test edilmiştir. Default, Manuscript, Bootstrap3, Health Sciences ve özel temalarla uyumludur.

## Lisans

GNU Genel Kamu Lisansı v3. Detaylar için [COPYING](COPYING) dosyasına bakın.

## Destek

- [GitHub Issues](https://github.com/ojs-services/trendMDojs/issues)
- [OJS Services](https://ojsservices.com)

**OJS Services** tarafından geliştirilmiştir.
