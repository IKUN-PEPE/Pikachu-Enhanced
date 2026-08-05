package vul.java_unserialize;

import java.io.*;
import java.util.Base64;

/**
 * Java Native Deserialization Executor Handler
 * Used by Pikachu-Enhanced to process ObjectInputStream.readObject() payloads
 */
public class JavaUnserializeHandler {

    public static void main(String[] args) {
        if (args.length < 1) {
            System.out.println("[ERROR] No Base64 payload provided.");
            return;
        }

        try {
            String base64Payload = args[0];
            byte[] payloadBytes = Base64.getDecoder().decode(base64Payload);

            System.out.println("[INFO] Magic Header: 0x" + 
                String.format("%02X%02X%02X%02X", payloadBytes[0], payloadBytes[1], payloadBytes[2], payloadBytes[3]));

            ByteArrayInputStream bais = new ByteArrayInputStream(payloadBytes);
            ObjectInputStream ois = new ObjectInputStream(bais);

            System.out.println("[INFO] Executing ObjectInputStream.readObject()...");
            Object obj = ois.readObject();

            System.out.println("[SUCCESS] Deserialized Class: " + obj.getClass().getName());

        } catch (Exception e) {
            System.out.println("[EXCEPTION] " + e.getMessage());
            e.printStackTrace();
        }
    }
}
