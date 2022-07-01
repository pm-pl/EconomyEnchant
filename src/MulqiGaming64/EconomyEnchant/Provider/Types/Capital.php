<?php

/*
 *  __  __       _       _  ____                 _              __   _  _
 * |  \/  |_   _| | __ _(_)/ ___| __ _ _ __ ___ (_)_ __   __ _ / /_ | || |
 * | |\/| | | | | |/ _` | | |  _ / _` | '_ ` _ \| | '_ \ / _` | '_ \| || |_
 * | |  | | |_| | | (_| | | |_| | (_| | | | | | | | | | | (_| | (_) |__   _|
 * |_|  |_|\__,_|_|\__, |_|\____|\__,_|_| |_| |_|_|_| |_|\__, |\___/   |_|
 *                    |_|                                |___/
 *
 * Copyright (c) 2022 MulqiGaming64
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

declare(strict_types=1);

namespace MulqiGaming64\EconomyEnchant\Provider\Types;

use MulqiGaming64\EconomyEnchant\EconomyEnchant;
use MulqiGaming64\EconomyEnchant\Provider\Provider;
use pocketmine\player\Player;
use SOFe\Capital\Capital as CapitalPL;
use SOFe\Capital\CapitalException;
use SOFe\Capital\LabelSet;
use function is_callable;

class Capital extends Provider
{
	const CAPITAL_VERSION = "0.1.0";

	/** @var callable $callable */
	private $callable;

	private $selector;

	public function __construct()
	{
		$selector = EconomyEnchant::getInstance()->getSelector(); // Load Selector from Config
		CapitalPL::api(self::CAPITAL_VERSION, function(CapitalPL $api) use($selector){
		  $this->selector = $api->completeConfig($selector);
		});
	}

	/** @return void */
	public function setCallable(callable $callable) : void
	{
		$this->callable = $callable;
	}

	public function process(Player $player, int $amount) : void
	{
		CapitalPL::api(self::CAPITAL_VERSION, function(CapitalPL $api) use($player, $amount){
			try {
				yield from $api->takeMoney(
					"EconomyEnchant",
					$player,
					$this->selector,
					$amount,
					new LabelSet(["reason" => "Buy a Enchantment from EconomyEnchant"]),
				);
				$this->handle(EconomyEnchant::STATUS_SUCCESS);
			} catch(CapitalException $error) {
				$this->handle(EconomyEnchant::STATUS_ENOUGH);
		   }
		});
	}

	/** @param int $status */
	public function handle(int $status) : void
	{
		if (is_callable($this->callable)) {
			$call = $this->callable;
			$call($status);
		}
	}
}
