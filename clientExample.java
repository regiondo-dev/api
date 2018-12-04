import java.util.ArrayList;
import java.util.Collections;
import java.util.Date;
import java.util.List;

import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;

import org.apache.commons.codec.binary.Hex;
import org.apache.commons.lang3.StringUtils;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.http.HttpEntity;
import org.springframework.http.HttpHeaders;
import org.springframework.http.HttpMethod;
import org.springframework.http.MediaType;
import org.springframework.web.client.HttpClientErrorException;
import org.springframework.web.client.RestTemplate;
import org.springframework.web.util.UriComponents;
import org.springframework.web.util.UriComponentsBuilder;

import com.google.common.collect.ImmutableMap;

public class RegiondoApiExample {

	private static final String HEADER_PUBLIC_KEY = "X-API-ID";
	private static final String HEADER_TIME = "X-API-TIME";
	private static final String HEADER_HMAC = "X-API-HASH";

	private static final String HMAC_ALGO = "HmacSHA256";

	private final static String BASE_URI = "https://sandbox-api.regiondo.com/v1";
	private final static String PRODUCT_LIST_URI = BASE_URI + "/products?sandboxauth=false&limit={limit}&offset={offset}";

	private static final String YOUR_PUBLIC_KEY = "<publicKey>";
	private static final String YOUR_SECRET_KEY = "<seceretKey>";

    // produced by @Configuration
    @Autowired
	@Qualifier("regiondoRestApi")
	private RestTemplate regiondoRestApi;

	public static String encodeHmacSha256(String key, String data) throws Exception {
		Mac sha256Hmac = Mac.getInstance(HMAC_ALGO);
		SecretKeySpec secretKey = new SecretKeySpec(key.getBytes("UTF-8"), HMAC_ALGO);
		sha256Hmac.init(secretKey);

		return Hex.encodeHexString(sha256Hmac.doFinal(data.getBytes("UTF-8")));
	}


	public String getProductsJson() {
		ImmutableMap<String, String> queryParams = new ImmutableMap.Builder<String, String>()
				.put("limit", "100")
				.put("offset", "0")
				.build();
		UriComponents c = UriComponentsBuilder.fromUriString(PRODUCT_LIST_URI)
				.buildAndExpand(queryParams);

		long ts = new Date().getTime();
		String time = String.valueOf(ts);

		String uri = c.toUriString();

		String hmacInput = time + YOUR_PUBLIC_KEY + StringUtils.substringAfter(uri, "?");

		HttpHeaders headers = new HttpHeaders();
		List<MediaType> mediaTypeList = new ArrayList<>();
		mediaTypeList.add(MediaType.APPLICATION_JSON);
		headers.setAccept(mediaTypeList);
		headers.setContentType(MediaType.APPLICATION_JSON);
		try {
			String hmac = encodeHmacSha256(YOUR_SECRET_KEY, hmacInput);
			headers.set(HEADER_HMAC, hmac);
			headers.set(HEADER_TIME, time);
			headers.set(HEADER_PUBLIC_KEY, YOUR_PUBLIC_KEY);
			headers.set("Accept-Language", "de-DE");
		} catch (Exception e) {
			throw new RuntimeException(e);
		}

		HttpEntity<?> requestEntity = new HttpEntity<>(headers);

        // since we want to cache we fetch the JSON directly and do the transformation in POJOs after DB operations
        try {
			String responseJson = regiondoRestApi.exchange(uri, HttpMethod.GET, requestEntity, String.class, Collections.EMPTY_LIST).getBody();
            return responseJson;
        } catch (HttpClientErrorException httpcee) {
			throw new RuntimeException(httpcee);
		}
    }
}
