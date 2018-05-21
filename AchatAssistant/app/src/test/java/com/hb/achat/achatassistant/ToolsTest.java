package com.hb.achat.achatassistant;

import org.junit.Test;

import static org.junit.Assert.*;

public class ToolsTest {

    @Test
    public void md5() {
        String enStr = "English:abcd1234";
        System.out.println(enStr);
        String result = Tools.md5(enStr);
        System.out.println(result);

        String chStr = "中文：中国文字";
        System.out.println(chStr);
        result = Tools.md5(chStr);
        System.out.println(result);
    }
}