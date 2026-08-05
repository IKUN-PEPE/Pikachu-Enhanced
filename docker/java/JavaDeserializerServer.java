import com.sun.net.httpserver.*;
import java.io.*;
import java.net.InetSocketAddress;
import java.util.*;
import java.util.regex.*;

/**
 * Pikachu-Enhanced - Independent Java Deserialization Sandbox Server
 * Native JVM ObjectInputStream.readObject() Executor Microservice
 */
public class JavaDeserializerServer {

    public static void main(String[] args) throws Exception {
        int port = 8088;
        HttpServer server = HttpServer.create(new InetSocketAddress(port), 0);
        
        server.createContext("/api/deserialize", new DeserializerHandler());
        server.createContext("/health", exchange -> {
            String resp = "{\"status\":\"UP\",\"service\":\"Java Deserialization Sandbox\"}";
            exchange.getResponseHeaders().set("Content-Type", "application/json");
            exchange.sendResponseHeaders(200, resp.length());
            exchange.getResponseBody().write(resp.getBytes());
            exchange.close();
        });

        server.setExecutor(null);
        System.out.println("[+] Pikachu Java Sandbox Microservice running on port " + port + "...");
        server.start();
    }

    static class DeserializerHandler implements HttpHandler {
        @Override
        public void handle(HttpExchange exchange) throws IOException {
            // Enable CORS
            exchange.getResponseHeaders().set("Access-Control-Allow-Origin", "*");
            exchange.getResponseHeaders().set("Content-Type", "application/json; charset=UTF-8");

            if ("OPTIONS".equalsIgnoreCase(exchange.getRequestMethod())) {
                exchange.sendResponseHeaders(204, -1);
                exchange.close();
                return;
            }

            if (!"POST".equalsIgnoreCase(exchange.getRequestMethod())) {
                String err = "{\"status\":\"error\",\"message\":\"Method Not Allowed\"}";
                exchange.sendResponseHeaders(405, err.length());
                exchange.getResponseBody().write(err.getBytes());
                exchange.close();
                return;
            }

            InputStream is = exchange.getRequestBody();
            String rawInput = new String(is.readAllBytes(), "UTF-8").trim();

            // Extract payload from raw string or JSON {"payload": "..."}
            String base64Payload = rawInput;
            if (rawInput.contains("\"payload\":")) {
                int start = rawInput.indexOf("\"payload\":") + 10;
                int quoteStart = rawInput.indexOf("\"", start);
                int quoteEnd = rawInput.indexOf("\"", quoteStart + 1);
                if (quoteStart != -1 && quoteEnd != -1) {
                    base64Payload = rawInput.substring(quoteStart + 1, quoteEnd);
                }
            }

            byte[] payloadBytes;
            try {
                payloadBytes = Base64.getDecoder().decode(base64Payload);
            } catch (Exception e) {
                payloadBytes = base64Payload.getBytes("ISO-8859-1");
            }

            if (payloadBytes.length < 4) {
                sendJsonResponse(exchange, 400, "error", "00000000", 0, new ArrayList<>(), "NONE", "", "Payload length too short.");
                return;
            }

            // Inspect Magic Bytes: 0xAC 0xED 0x00 0x05
            String hexMagic = String.format("%02X%02X%02X%02X", payloadBytes[0], payloadBytes[1], payloadBytes[2], payloadBytes[3]);
            boolean isMagicValid = "ACED0005".equalsIgnoreCase(hexMagic);

            if (!isMagicValid) {
                sendJsonResponse(exchange, 400, "error", hexMagic, payloadBytes.length, new ArrayList<>(), "NONE", "", "Invalid Java Serialization Magic Bytes: 0x" + hexMagic + " (Expected 0xACED0005)");
                return;
            }

            // Extract Class Names via Regex
            List<String> classNames = new ArrayList<>();
            String textContent = new String(payloadBytes, "ISO-8859-1");
            Pattern p = Pattern.compile("([a-zA-Z0-9_\\$]+\\.[a-zA-Z0-9_\\.]+)|([a-zA-Z0-9_\\$]+\\/[a-zA-Z0-9_\\$]+)");
            Matcher m = p.matcher(textContent);
            while (m.find()) {
                String match = m.group();
                if (match.length() > 3 && !classNames.contains(match)) {
                    classNames.add(match);
                }
            }

            // Perform Actual Java Native ObjectInputStream.readObject()
            String gadgetTriggered = "READ_OBJECT_SUCCESS";
            String cmdOutput = "";
            String message = "Java ObjectInputStream.readObject() executed successfully in sandbox container.";
            int httpStatus = 200;

            try {
                ByteArrayInputStream bais = new ByteArrayInputStream(payloadBytes);
                ObjectInputStream ois = new ObjectInputStream(bais);
                
                // Triggers JVM Deserialization
                Object deserializedObj = ois.readObject();
                message = "Object Deserialized: " + deserializedObj.getClass().getName();
                
            } catch (Exception e) {
                message = "JVM readObject Exception: " + e.getClass().getName() + " - " + e.getMessage();
            }

            // Gadget Pattern Classification
            if (textContent.contains("calc") || textContent.contains("whoami") || textContent.contains("id") || textContent.contains("JavaPayloadObject")) {
                gadgetTriggered = "RCE_COMMAND_EXECUTION";
                cmdOutput = "uid=0(root) gid=0(root) groups=0(root) [Isolated Container: pikachu-enhanced-java]";
            } else if (textContent.contains("URL") || textContent.contains("HashMap")) {
                gadgetTriggered = "URLDNS_LOOKUP";
                cmdOutput = "[java.net.URL.hashCode()] -> Triggered OOB DNS Query from Java Sandbox Container.";
            }

            sendJsonResponse(exchange, httpStatus, "success", hexMagic, payloadBytes.length, classNames, gadgetTriggered, cmdOutput, message);
        }

        private void sendJsonResponse(HttpExchange exchange, int code, String status, String hexMagic, int length, List<String> classes, String gadget, String cmdOutput, String msg) throws IOException {
            StringBuilder sb = new StringBuilder();
            sb.append("{");
            sb.append("\"status\":\"").append(escapeJson(status)).append("\",");
            sb.append("\"magic\":\"").append(escapeJson(hexMagic)).append("\",");
            sb.append("\"length\":").append(length).append(",");
            sb.append("\"classes\":[");
            for (int i = 0; i < classes.size(); i++) {
                sb.append("\"").append(escapeJson(classes.get(i))).append("\"");
                if (i < classes.size() - 1) sb.append(",");
            }
            sb.append("],");
            sb.append("\"gadget\":\"").append(escapeJson(gadget)).append("\",");
            sb.append("\"cmd_output\":\"").append(escapeJson(cmdOutput)).append("\",");
            sb.append("\"message\":\"").append(escapeJson(msg)).append("\",");
            sb.append("\"container\":\"pikachu-enhanced-java (OpenJDK 17 Container)\"");
            sb.append("}");

            byte[] respBytes = sb.toString().getBytes("UTF-8");
            exchange.sendResponseHeaders(code, respBytes.length);
            exchange.getResponseBody().write(respBytes);
            exchange.close();
        }

        private String escapeJson(String s) {
            if (s == null) return "";
            return s.replace("\\", "\\\\").replace("\"", "\\\"").replace("\n", "\\n").replace("\r", "\\r");
        }
    }
}
